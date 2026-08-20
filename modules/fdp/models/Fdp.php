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
            [['start_date', 'end_date'], 'validateDates'],
            [['time_start', 'time_end'], 'validateTimes'],
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

    public function validateDates(string $attribute): void
    {
        $start = $this->start_date ? new \DateTime($this->start_date) : null;
        $end = $this->end_date ? new \DateTime($this->end_date) : null;
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        if ($start && $start < $today) {
            $this->addError('start_date', 'Start date cannot be in the past.');
        }

        if ($start && $end && $end < $start) {
            $this->addError('end_date', 'End date must be the same as or after the start date.');
        }
    }

    public function validateTimes(string $attribute): void
    {
        $ts = trim((string) $this->time_start);
        $te = trim((string) $this->time_end);

        if ($ts === '' || $te === '') {
            return;
        }

        $startDate = $this->start_date ?? null;
        $endDate = $this->end_date ?? null;

        // if dates are set and equal, ensure times are in correct order
        if ($startDate && $endDate && $startDate === $endDate) {
            try {
                $startTime = \DateTime::createFromFormat('H:i', $ts);
                $endTime = \DateTime::createFromFormat('H:i', $te);
                if ($startTime && $endTime && $endTime < $startTime) {
                    $this->addError('time_end', 'End time must be the same as or after the start time when on the same date.');
                }
            } catch (\Throwable $e) {
                // ignore parse errors, other validators cover formats
            }
        }
    }

    public function getAttendanceRecords()
    {
        return $this->hasMany(FdpAttendance::class, ['fdp_id' => 'id']);
    }

    public function getParticipants()
    {
        return $this->hasMany(FdpParticipant::class, ['fdp_id' => 'id']);
    }
}
