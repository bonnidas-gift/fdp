<?php

declare(strict_types=1);

namespace app\modules\fdp\models;

use yii\db\ActiveRecord;

class FdpAttendance extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'fdp_attendance';
    }

    public function rules(): array
    {
        return [
            [['fdp_id', 'faculty_name', 'status'], 'required'],
            ['faculty_email', 'email'],
            ['status', 'in', 'range' => array_keys(self::statusOptions())],
            ['fdp_id', 'integer'],
            ['notes', 'string'],
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'Present' => 'Present',
            'Absent' => 'Absent',
            'On Duty' => 'On Duty',
            'Leave' => 'Leave',
        ];
    }

    public static function normalizeStatus(string $status): string
    {
        $normalized = strtolower(trim($status));

        $map = [
            'present' => 'Present',
            'absent' => 'Absent',
            'on duty' => 'On Duty',
            'od' => 'On Duty',
            'leave' => 'Leave',
        ];

        return $map[$normalized] ?? 'Present';
    }

    public static function readCsvRows(string $filePath): array
    {
        $rows = [];
        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            return $rows;
        }

        $header = null;

        while (($data = fgetcsv($handle)) !== false) {
            if ($header === null) {
                $header = array_map(static fn ($value) => strtolower(trim((string) $value)), $data);
                continue;
            }

            $row = [];
            foreach ($header as $index => $key) {
                $row[$key] = $data[$index] ?? '';
            }

            $rows[] = [
                'name' => $row['name'] ?? $row['faculty'] ?? '',
                'email' => $row['email'] ?? $row['faculty_email'] ?? '',
                'status' => self::normalizeStatus((string) ($row['status'] ?? $row['attendance'] ?? 'Present')),
            ];
        }

        fclose($handle);

        return $rows;
    }

    public function getFdp()
    {
        return $this->hasOne(Fdp::class, ['id' => 'fdp_id']);
    }
}
