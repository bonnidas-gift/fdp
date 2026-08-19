<?php

declare(strict_types=1);

namespace tests\Unit;

use app\modules\fdp\models\FdpMailService;
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
}
