<?php

declare(strict_types=1);

namespace app\modules\fdp\commands;

use app\modules\fdp\models\Fdp;
use app\modules\fdp\models\FdpMailQueue;
use app\modules\fdp\models\FdpMailService;
use app\modules\fdp\models\FdpParticipant;
use DateTimeImmutable;
use DateTimeZone;
use yii\console\Controller;
use yii\console\ExitCode;

class ReminderController extends Controller
{
    public function actionSend(): int
    {
        $today = new DateTimeImmutable('today', new DateTimeZone('UTC'));
        $today = $today->setTime(0, 0, 0);

        $fdps = Fdp::find()->all();

        foreach ($fdps as $fdp) {
            $startDate = DateTimeImmutable::createFromFormat('!Y-m-d', $fdp->start_date, new DateTimeZone('UTC'));
            if ($startDate === false) {
                continue;
            }
            $startDate = $startDate->setTime(0, 0, 0);

            if (!FdpMailService::isReminderDue($fdp->start_date, $today->format('Y-m-d'))) {
                continue;
            }

            if ($fdp->reminder_sent_at !== null && $fdp->reminder_sent_at !== '') {
                $sentAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $fdp->reminder_sent_at, new DateTimeZone('UTC'));
                if ($sentAt !== false && $sentAt->setTime(0, 0, 0)->format('Y-m-d') === $startDate->format('Y-m-d')) {
                    continue;
                }
            }

            $participants = FdpParticipant::find()->where(['fdp_id' => $fdp->id])->all();
            if ($participants === []) {
                continue;
            }

            foreach ($participants as $participant) {
                $mail = FdpMailService::buildReminderMail([
                    'title' => $fdp->title,
                    'start_date' => $fdp->start_date,
                    'end_date' => $fdp->end_date,
                    'time' => $fdp->time,
                    'mode' => $fdp->mode,
                    'venue' => $fdp->venue ?: $fdp->meeting_link,
                    'coordinator' => $fdp->coordinator_name,
                ]);

                FdpMailQueue::queue(
                    'reminder',
                    (int) $fdp->id,
                    (string) $participant->faculty_email,
                    $mail['subject'],
                    $mail['body'],
                    $mail['htmlBody']
                );
            }

            $fdp->reminder_sent_at = $today->format('Y-m-d H:i:s');
            $fdp->save(false);

            $queued = FdpMailQueue::processPending();
            echo "Queued and sent {$queued} reminder mail(s) for FDP #{$fdp->id}: {$fdp->title}\n";
        }

        return ExitCode::OK;
    }
}
