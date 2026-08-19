<?php

declare(strict_types=1);

namespace app\commands;

use app\modules\fdp\models\Fdp;
use app\modules\fdp\models\FdpAttendance;
use app\modules\fdp\models\FdpMailService;
use yii\console\Controller;
use yii\console\ExitCode;

class FdpController extends Controller
{
    public function actionReminder(int $id): int
    {
        $fdp = Fdp::findOne($id);

        if ($fdp === null) {
            echo "FDP not found.\n";

            return ExitCode::DATAERR;
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

        echo "Subject: {$mail['subject']}\n";
        echo "Body:\n{$mail['body']}\n";

        return ExitCode::OK;
    }

    public function actionDefaulters(int $id): int
    {
        $fdp = Fdp::findOne($id);

        if ($fdp === null) {
            echo "FDP not found.\n";

            return ExitCode::DATAERR;
        }

        $records = $fdp->getAttendanceRecords()->where(['status' => 'Absent'])->all();

        if (empty($records)) {
            echo "No defaulters found.\n";

            return ExitCode::OK;
        }

        foreach ($records as $record) {
            $mail = FdpMailService::buildDefaulterMail([
                'title' => $fdp->title,
                'date' => $fdp->start_date,
            ], ['name' => $record->faculty_name]);

            echo "To: {$record->faculty_email}\n";
            echo "Subject: {$mail['subject']}\n";
            echo "Body:\n{$mail['body']}\n\n";
        }

        return ExitCode::OK;
    }

    public function actionStatus(int $id): int
    {
        $fdp = Fdp::findOne($id);

        if ($fdp === null) {
            echo "FDP not found.\n";

            return ExitCode::DATAERR;
        }

        $records = $fdp->getAttendanceRecords()->all();
        $summary = [
            'Present' => 0,
            'Absent' => 0,
            'On Duty' => 0,
            'Leave' => 0,
        ];

        foreach ($records as $record) {
            $status = FdpAttendance::normalizeStatus((string) $record->status);
            if (isset($summary[$status])) {
                $summary[$status]++;
            }
        }

        foreach ($summary as $status => $count) {
            echo "{$status}: {$count}\n";
        }

        return ExitCode::OK;
    }
}
