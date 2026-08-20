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

        // provide explicit parameters for fgetcsv to avoid future PHP warnings
        while (($data = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
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

    public static function readXlsxRows(string $filePath): array
    {
        if (!class_exists('ZipArchive')) {
            return [];
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $xml = simplexml_load_string((string) $sharedStringsXml);
            if ($xml !== false) {
                foreach ($xml->si as $item) {
                    $parts = [];
                    foreach ($item->children('http://schemas.openxmlformats.org/spreadsheetml/2006/main') as $node) {
                        $parts[] = (string) $node;
                    }
                    $sharedStrings[] = implode('', $parts);
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            return [];
        }

        $sheet = simplexml_load_string((string) $sheetXml);
        if ($sheet === false) {
            return [];
        }

        $sheetRows = [];
        foreach ($sheet->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $cell) {
                $cellType = (string) $cell['t'];
                $cellRef = (string) $cell['r'];
                preg_match('/([A-Z]+)/', $cellRef, $matches);
                $column = $matches[1] ?? 'A';
                $value = '';

                if ($cellType === 's' && isset($cell->v)) {
                    $index = (int) $cell->v;
                    $value = $sharedStrings[$index] ?? '';
                } elseif ($cellType === 'inlineStr' && isset($cell->is)) {
                    $value = (string) $cell->is->t;
                } elseif (isset($cell->v)) {
                    $value = (string) $cell->v;
                }

                $cells[$column] = trim((string) $value);
            }

            ksort($cells);
            if ($cells !== []) {
                $sheetRows[] = array_values($cells);
            }
        }

        if (empty($sheetRows)) {
            return [];
        }

        $header = array_map(static fn ($value) => strtolower(trim((string) $value)), $sheetRows[0]);
        $rows = [];
        foreach (array_slice($sheetRows, 1) as $row) {
            $entry = [];
            foreach ($header as $index => $key) {
                $entry[$key] = $row[$index] ?? '';
            }

            $rows[] = [
                'name' => $entry['name'] ?? $entry['faculty'] ?? '',
                'email' => FdpParticipant::normalizeEmail((string) ($entry['email'] ?? $entry['faculty_email'] ?? '')),
                'status' => self::normalizeStatus((string) ($entry['status'] ?? $entry['attendance'] ?? 'Present')),
            ];
        }

        return $rows;
    }

    public static function splitValidAndSkippedRowsForFdp(int $fdpId, array $rows, array $participantIndex = [], array $existingEmails = []): array
    {
        if ($participantIndex === []) {
            $participants = FdpParticipant::find()->where(['fdp_id' => $fdpId])->all();
            $participantIndex = [];
            foreach ($participants as $participant) {
                $email = FdpParticipant::normalizeEmail((string) $participant->faculty_email);
                if ($email !== '') {
                    $participantIndex[$email] = [
                        'fdp_id' => (int) $participant->fdp_id,
                        'faculty_name' => (string) $participant->faculty_name,
                        'faculty_email' => FdpParticipant::normalizeEmail((string) $participant->faculty_email),
                    ];
                }
            }
        }

        $existingEmails = array_fill_keys(array_map(static fn ($value) => FdpParticipant::normalizeEmail((string) $value), $existingEmails), true);

        $valid = [];
        $skipped = [];
        foreach ($rows as $row) {
            $email = FdpParticipant::normalizeEmail((string) ($row['email'] ?? $row['faculty_email'] ?? ''));
            if ($email === '' || !isset($participantIndex[$email])) {
                $skipped[] = [
                    'name' => (string) ($row['name'] ?? $row['faculty'] ?? $row['faculty_name'] ?? ''),
                    'email' => $email,
                    'status' => self::normalizeStatus((string) ($row['status'] ?? $row['attendance'] ?? 'Present')),
                    'reason' => $email === '' ? 'Missing email' : 'Not found in FDP participant list',
                ];
                continue;
            }

            if (isset($existingEmails[$email])) {
                $skipped[] = [
                    'name' => (string) ($row['name'] ?? $row['faculty'] ?? $row['faculty_name'] ?? ''),
                    'email' => $email,
                    'status' => self::normalizeStatus((string) ($row['status'] ?? $row['attendance'] ?? 'Present')),
                    'reason' => 'Attendance already exists for this participant in this FDP',
                ];
                continue;
            }

            $participant = $participantIndex[$email];
            $valid[] = [
                'fdp_id' => $fdpId,
                'faculty_name' => (string) ($row['name'] ?? $participant['faculty_name'] ?? ''),
                'faculty_email' => $participant['faculty_email'],
                'status' => self::normalizeStatus((string) ($row['status'] ?? $row['attendance'] ?? 'Present')),
            ];
            $existingEmails[$email] = true;
        }

        return ['valid' => $valid, 'skipped' => $skipped];
    }

    public static function filterValidRowsForFdp(int $fdpId, array $rows, array $participantIndex = []): array
    {
        return self::splitValidAndSkippedRowsForFdp($fdpId, $rows, $participantIndex)['valid'];
    }

    public function getFdp()
    {
        return $this->hasOne(Fdp::class, ['id' => 'fdp_id']);
    }
}
