<?php

declare(strict_types=1);

namespace app\modules\fdp\models;

class FdpMailService
{
    public static function buildReminderMail(array $fdp): array
    {
        $title = $fdp['title'] ?? 'Faculty Development Programme';
        $startDate = $fdp['start_date'] ?? ($fdp['date'] ?? 'TBD');
        $endDate = $fdp['end_date'] ?? $startDate;
        $time = $fdp['time'] ?? 'TBD';
        $mode = $fdp['mode'] ?? 'Online';
        $venue = $fdp['venue'] ?? 'To be announced';
        $coordinator = $fdp['coordinator'] ?? 'FDP Coordinator';

        $subject = sprintf('Reminder: Participation in FDP on %s', $title);
        $body = <<<MAIL
Dear Faculty Members,

This is a gentle reminder to attend the Faculty Development Programme (FDP) on {$title}, scheduled to be held from {$startDate} to {$endDate}.

Details of the FDP:
• Topic: {$title}
• Date & Time: {$time}
• Mode: {$mode}
• Venue/Link: {$venue}
• Coordinator: {$coordinator}

All nominated faculty members are requested to attend the programme as per the schedule and ensure active participation.
Your presence will be recorded in the CMS and performance tracking.

Regards,
FDP Coordination Team
MAIL;

        return ['subject' => $subject, 'body' => $body];
    }

    public static function buildDefaulterMail(array $fdp, array $recipient = []): array
    {
        $title = $fdp['title'] ?? 'Faculty Development Programme';
        $date = $fdp['date'] ?? ($fdp['start_date'] ?? 'TBD');
        $name = $recipient['name'] ?? 'Sir/Madam';

        $subject = sprintf('Notice Regarding Absence from FDP on %s', $title);
        $body = <<<MAIL
Dear {$name},

It has been observed that you were marked absent for the Faculty Development Programme (FDP) on {$title}, conducted on {$date}.

FDP Details:
• Title: {$title}
• Date: {$date}

As participation in FDPs is an important academic and institutional requirement, your absence has been recorded in the CMS.
If your absence was due to valid reasons (On Duty/Leave), you are requested to inform the FDP coordinator at the earliest for necessary updates.
Kindly treat this as important.

Regards,
FDP Coordination Team
MAIL;

        return ['subject' => $subject, 'body' => $body];
    }
}
