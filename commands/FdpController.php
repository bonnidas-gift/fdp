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
