<?php

declare(strict_types=1);

namespace app\modules\fdp\models;

use yii\db\ActiveRecord;

class Fdp extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'fdp';
    }

    public function rules(): array
    {
        return [
            [['title', 'start_date', 'end_date', 'mode', 'coordinator_name'], 'required'],
            [['title', 'mode', 'venue', 'meeting_link', 'coordinator_name'], 'string', 'max' => 255],
            [['time'], 'string', 'max' => 100],
            [['start_date', 'end_date'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'title' => 'FDP Title',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'time' => 'Date & Time',
            'mode' => 'Mode',
            'venue' => 'Venue',
            'meeting_link' => 'Meeting Link',
            'coordinator_name' => 'Coordinator',
        ];
    }

    public function getAttendanceRecords()
    {
        return $this->hasMany(FdpAttendance::class, ['fdp_id' => 'id']);
    }
}
