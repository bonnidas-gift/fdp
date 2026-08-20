<?php

declare(strict_types=1);

namespace app\modules\fdp\models;

use yii\db\ActiveRecord;

class FdpParticipant extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'fdp_participant';
    }

    public function rules(): array
    {
        return [
            [['fdp_id', 'faculty_name', 'faculty_email'], 'required'],
            ['faculty_email', 'email'],
            [['fdp_id'], 'integer'],
            [['faculty_name', 'faculty_email', 'department', 'designation'], 'string', 'max' => 255],
        ];
    }

    public static function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public static function readCsvRows(string $filePath): array
    {
        $rows = [];
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            return $rows;
        }

        $header = null;
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
                'name' => $row['name'] ?? $row['faculty'] ?? $row['faculty_name'] ?? '',
                'email' => self::normalizeEmail((string) ($row['email'] ?? $row['faculty_email'] ?? '')),
                'department' => $row['department'] ?? '',
                'designation' => $row['designation'] ?? '',
            ];
        }

        fclose($handle);

        return $rows;
    }

    public static function emailExistsForFdp(int $fdpId, string $email): bool
    {
        $normalized = self::normalizeEmail($email);
        if ($normalized === '') {
            return false;
        }

        return self::find()->where(['fdp_id' => $fdpId])->andWhere(['LOWER(faculty_email)' => $normalized])->exists();
    }

    public static function filterUniqueRowsForFdp(int $fdpId, array $rows): array
    {
        $seen = [];
        $unique = [];
        $skipped = [];

        foreach ($rows as $row) {
            $email = self::normalizeEmail((string) ($row['email'] ?? ''));
            if ($email === '') {
                $skipped[] = ['row' => $row, 'reason' => 'Missing email'];
                continue;
            }

            if (isset($seen[$email]) || self::emailExistsForFdp($fdpId, $email)) {
                $skipped[] = ['row' => $row, 'reason' => 'Participant already exists for this FDP'];
                continue;
            }

            $seen[$email] = true;
            $unique[] = $row;
        }

        return ['valid' => $unique, 'skipped' => $skipped];
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
                'name' => $entry['name'] ?? $entry['faculty'] ?? $entry['faculty_name'] ?? '',
                'email' => self::normalizeEmail((string) ($entry['email'] ?? $entry['faculty_email'] ?? '')),
                'department' => $entry['department'] ?? '',
                'designation' => $entry['designation'] ?? '',
            ];
        }

        return $rows;
    }

    public function getFdp()
    {
        return $this->hasOne(Fdp::class, ['id' => 'fdp_id']);
    }
}
