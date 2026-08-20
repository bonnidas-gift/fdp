<?php

declare(strict_types=1);

namespace app\modules\fdp\commands;

use app\modules\fdp\models\Fdp;
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
        $targetDate = $today->modify('+1 day')->format('Y-m-d');

        $fdps = Fdp::find()->where(['<=', 'start_date', $targetDate])->andWhere(['>=', 'start_date', $targetDate])->all();

        foreach ($fdps as $fdp) {
            if ($fdp->reminder_sent_at !== null && $fdp->reminder_sent_at !== '') {
                $sentAt = new DateTimeImmutable($fdp->reminder_sent_at, new DateTimeZone('UTC'));
                if ($sentAt->format('Y-m-d') === $targetDate) {
                    continue;
                }
            }

            $participants = FdpParticipant::find()->where(['fdp_id' => $fdp->id])->all();
            if ($participants === []) {
                continue;
            }

            $mail = FdpMailService::buildReminderMail([
                'title' => $fdp->title,
                'start_date' => $fdp->start_date,
                'end_date' => $fdp->end_date,
                'time' => $fdp->time,
                'mode' => $fdp->mode,
                'venue' => $fdp->venue ?: $fdp->meeting_link,
                'coordinator' => $fdp->coordinator_name,
            ]);

            foreach ($participants as $participant) {
                $this->sendMail((string) $participant->faculty_email, $mail['subject'], $mail['body']);
            }

            $fdp->reminder_sent_at = $today->format('Y-m-d H:i:s');
            $fdp->save(false);
            echo "Sent reminder for FDP #{$fdp->id}: {$fdp->title}\n";
        }

        return ExitCode::OK;
    }

    protected function sendMail(string $to, string $subject, string $body): bool
    {
        $headers = [
            'From: noreply@localhost',
            'Reply-To: noreply@localhost',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
        ];

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }
}
