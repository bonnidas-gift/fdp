<?php

declare(strict_types=1);

namespace app\modules\fdp\commands;

use app\modules\fdp\models\Fdp;
use app\modules\fdp\models\FdpMailQueue;
use app\modules\fdp\models\FdpMailService;
use yii\console\Controller;
use yii\console\ExitCode;

class DefaulterController extends Controller
{
    public $fdpId;

    public function options($actionID): array
    {
        return array_merge(parent::options($actionID), ['fdpId']);
    }

    public function actionSend(?int $fdpId = null): int
    {
        $fdpId = $fdpId ?? (int) $this->fdpId;

        if ($fdpId > 0) {
            $fdps = [Fdp::findOne($fdpId)];
        } else {
            $fdps = Fdp::find()->orderBy(['id' => SORT_ASC])->all();
        }

        if ($fdps === [] || $fdps[0] === null) {
            echo "FDP not found.\n";

            return ExitCode::DATAERR;
        }

        $queued = 0;
        foreach ($fdps as $fdp) {
            if ($fdp === null) {
                continue;
            }

            $records = $fdp->getAttendanceRecords()->where(['status' => 'Absent'])->all();
            if (empty($records)) {
                continue;
            }

            foreach ($records as $record) {
                $mail = FdpMailService::buildDefaulterMail([
                    'title' => $fdp->title,
                    'date' => $fdp->start_date,
                ], ['name' => $record->faculty_name]);

                $inserted = FdpMailQueue::queue(
                    'defaulter',
                    (int) $fdp->id,
                    (string) $record->faculty_email,
                    $mail['subject'],
                    $mail['body'],
                    $mail['htmlBody']
                );

                if ($inserted) {
                    $queued++;
                    echo "Queued defaulter mail for {$record->faculty_email}\n";
                }
            }
        }

        $sent = FdpMailQueue::processPending();
        echo "Queued {$queued} new mail(s). Sent {$sent} queued mail(s).\n";

        return ExitCode::OK;
    }
}
