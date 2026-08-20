<?php

declare(strict_types=1);

namespace tests\Unit;

use app\modules\fdp\models\FdpAttendance;
use app\modules\fdp\models\FdpMailService;
use app\modules\fdp\models\FdpParticipant;
use PHPUnit\Framework\TestCase;

final class FdpModuleTest extends TestCase
{
    public function testReminderMailSubjectAndBodyContainFdpDetails(): void
    {
        $mail = FdpMailService::buildReminderMail([
            'title' => 'AI in Teaching',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-03',
            'time' => '09:30 AM - 04:30 PM',
            'mode' => 'Online',
            'venue' => 'https://meet.example.com/fdp',
            'coordinator' => 'Dr. Smith',
        ]);

        $this->assertSame('Reminder: Participation in FDP on AI in Teaching', $mail['subject']);
        $this->assertStringContainsString('AI in Teaching', $mail['body']);
        $this->assertStringContainsString('https://meet.example.com/fdp', $mail['body']);
    }

    public function testDefaulterMailSubjectAndBodyContainAbsenceNotice(): void
    {
        $mail = FdpMailService::buildDefaulterMail([
            'title' => 'Research Methodology',
            'date' => '2026-07-15',
        ], [
            'name' => 'Prof. Kumar',
        ]);

        $this->assertSame('Notice Regarding Absence from FDP on Research Methodology', $mail['subject']);
        $this->assertStringContainsString('Prof. Kumar', $mail['body']);
        $this->assertStringContainsString('Research Methodology', $mail['body']);
        $this->assertStringContainsString('If your absence was due to valid reasons', $mail['body']);
    }

    public function testParticipantEmailIsNormalizedBeforeMatching(): void
    {
        $this->assertSame('alice@example.com', FdpParticipant::normalizeEmail(' Alice@Example.com '));
        $this->assertSame('bob@example.com', FdpParticipant::normalizeEmail('BOB@example.com'));
    }

    public function testAttendanceRowsAreFilteredToValidFdpParticipants(): void
    {
        $rows = [
            ['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'Present'],
            ['name' => 'Bob', 'email' => 'bob@example.com', 'status' => 'Absent'],
            ['name' => 'Unknown', 'email' => 'unknown@example.com', 'status' => 'Present'],
        ];

        $validRows = FdpAttendance::filterValidRowsForFdp(14, $rows, [
            'alice@example.com' => ['fdp_id' => 14, 'faculty_name' => 'Alice', 'faculty_email' => 'alice@example.com'],
            'bob@example.com' => ['fdp_id' => 14, 'faculty_name' => 'Bob', 'faculty_email' => 'bob@example.com'],
        ]);

        $this->assertCount(2, $validRows);
        $this->assertSame('Alice', $validRows[0]['faculty_name']);
        $this->assertSame('Bob', $validRows[1]['faculty_name']);
        $this->assertNotContains('unknown@example.com', array_column($validRows, 'faculty_email'));
    }

    public function testAttendanceRowsReportSkippedFacultiesNotInFdpParticipantList(): void
    {
        $rows = [
            ['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'Present'],
            ['name' => 'Unknown', 'email' => 'unknown@example.com', 'status' => 'Present'],
        ];

        $split = FdpAttendance::splitValidAndSkippedRowsForFdp(14, $rows, [
            'alice@example.com' => ['fdp_id' => 14, 'faculty_name' => 'Alice', 'faculty_email' => 'alice@example.com'],
        ]);

        $this->assertCount(1, $split['valid']);
        $this->assertCount(1, $split['skipped']);
        $this->assertSame('unknown@example.com', $split['skipped'][0]['email']);
    }

    public function testDuplicateAttendanceRowsAreMarkedSkippedForSameParticipantInSameFdp(): void
    {
        $rows = [
            ['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'Present'],
            ['name' => 'Alice', 'email' => 'Alice@Example.com', 'status' => 'Absent'],
        ];

        $split = FdpAttendance::splitValidAndSkippedRowsForFdp(14, $rows, [
            'alice@example.com' => ['fdp_id' => 14, 'faculty_name' => 'Alice', 'faculty_email' => 'alice@example.com'],
        ]);

        $this->assertCount(1, $split['valid']);
        $this->assertCount(1, $split['skipped']);
        $this->assertSame('alice@example.com', $split['skipped'][0]['email']);
        $this->assertSame('Attendance already exists for this participant in this FDP', $split['skipped'][0]['reason']);

        $alreadyExisting = FdpAttendance::splitValidAndSkippedRowsForFdp(14, $rows, [
            'alice@example.com' => ['fdp_id' => 14, 'faculty_name' => 'Alice', 'faculty_email' => 'alice@example.com'],
        ], ['alice@example.com']);

        $this->assertCount(0, $alreadyExisting['valid']);
        $this->assertCount(2, $alreadyExisting['skipped']);
    }

    public function testAttendanceXlsxRowsAreParsedFromWorkbook(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'fdp_attendance_');
        $this->assertNotFalse($tempFile);

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($tempFile, \ZipArchive::OVERWRITE) === true);
        $zip->addFromString('[Content_Types].xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
  <Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
</Types>
XML
        );
        $zip->addFromString('_rels/.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
XML
        );
        $zip->addFromString('docProps/core.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:creator>Copilot</dc:creator>
</cp:coreProperties>
XML
        );
        $zip->addFromString('docProps/app.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"></Properties>
XML
        );
        $zip->addFromString('xl/workbook.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Sheet1" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML
        );
        $zip->addFromString('xl/_rels/workbook.xml.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
</Relationships>
XML
        );
        $zip->addFromString('xl/sharedStrings.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="6" uniqueCount="6">
  <si><t>name</t></si>
  <si><t>email</t></si>
  <si><t>status</t></si>
  <si><t>Alice</t></si>
  <si><t>alice@example.com</t></si>
  <si><t>Present</t></si>
</sst>
XML
        );
        $zip->addFromString('xl/worksheets/sheet1.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetData>
    <row r="1">
      <c r="A1" t="s"><v>0</v></c>
      <c r="B1" t="s"><v>1</v></c>
      <c r="C1" t="s"><v>2</v></c>
    </row>
    <row r="2">
      <c r="A2" t="s"><v>3</v></c>
      <c r="B2" t="s"><v>4</v></c>
      <c r="C2" t="s"><v>5</v></c>
    </row>
  </sheetData>
</worksheet>
XML
        );
        $zip->close();

        $rows = FdpAttendance::readXlsxRows($tempFile);

        $this->assertSame([
            ['name' => 'Alice', 'email' => 'alice@example.com', 'status' => 'Present'],
        ], $rows);

        unlink($tempFile);
    }
}
