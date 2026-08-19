<?php

declare(strict_types=1);

namespace app\modules\fdp\models;

use yii\db\ActiveRecord;

class Fdp extends ActiveRecord
{
    public string $time_start = '';
    public string $time_end = '';

    public static function tableName(): string
    {
        return 'fdp';
    }

    public function beforeValidate(): bool
    {
        if (parent::beforeValidate()) {
            $start = trim((string) $this->time_start);
            $end = trim((string) $this->time_end);

            if ($start !== '' || $end !== '') {
                $this->time = trim($start . ' - ' . $end);
            }

            return true;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            [['title', 'start_date', 'end_date', 'mode', 'coordinator_name'], 'required'],
            [['title', 'mode', 'venue', 'meeting_link', 'coordinator_name'], 'string', 'max' => 255],
            [['time', 'time_start', 'time_end'], 'string', 'max' => 100],
            [['start_date', 'end_date'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'title' => 'FDP Title',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'time' => 'Time Range',
            'time_start' => 'Start Time',
            'time_end' => 'End Time',
            'mode' => 'Mode',
            'venue' => 'Venue',
            'meeting_link' => 'Meeting Link',
            'coordinator_name' => 'Coordinator',
        ];
    }

    public function modeOptions(): array
    {
        return [
            'Online' => 'Online',
            'Offline' => 'Offline',
            'Hybrid' => 'Hybrid',
        ];
    }

    public function getAttendanceRecords()
    {
        return $this->hasMany(FdpAttendance::class, ['fdp_id' => 'id']);
    }
}
