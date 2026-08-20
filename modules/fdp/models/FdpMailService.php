<?php

declare(strict_types=1);

namespace app\modules\fdp\models;

class FdpMailService
{
    public static function isReminderDue(string $startDate, ?string $currentDate = null): bool
    {
        $today = new \DateTimeImmutable($currentDate ?? 'today', new \DateTimeZone('UTC'));
        $today = $today->setTime(0, 0, 0);

        $start = \DateTimeImmutable::createFromFormat('!Y-m-d', $startDate, new \DateTimeZone('UTC'));
        if ($start === false) {
            return false;
        }

        $start = $start->setTime(0, 0, 0);

        return ($start->getTimestamp() - $today->getTimestamp()) === 86400;
    }

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

        $htmlBody = <<<HTML
<html>
<head>
    <meta charset="UTF-8">
    <title>{$subject}</title>
</head>
<body style="margin:0; padding:24px; background:#f4f6fb; font-family:Arial, sans-serif; color:#1f2937;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
        <div style="padding:24px 24px 12px; background:#0f172a; color:#ffffff; font-size:22px; font-weight:bold;">
            FDP Reminder
        </div>
        <div style="padding:24px;">
            <p style="margin:0 0 16px; font-size:16px;">Dear Faculty Members,</p>
            <p style="margin:0 0 16px; line-height:1.6;">
                This is a gentle reminder to attend the Faculty Development Programme (FDP) on <strong>{$title}</strong>,
                scheduled to be held from <strong>{$startDate}</strong> to <strong>{$endDate}</strong>.
            </p>

            <table cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0 0 16px; background:#f8fafc; border:1px solid #e5e7eb;">
                <tr>
                    <td style="font-weight:bold; width:35%; border-bottom:1px solid #e5e7eb;">Topic</td>
                    <td style="border-bottom:1px solid #e5e7eb;">{$title}</td>
                </tr>
                <tr>
                    <td style="font-weight:bold; border-bottom:1px solid #e5e7eb;">Date & Time</td>
                    <td style="border-bottom:1px solid #e5e7eb;">{$time}</td>
                </tr>
                <tr>
                    <td style="font-weight:bold; border-bottom:1px solid #e5e7eb;">Mode</td>
                    <td style="border-bottom:1px solid #e5e7eb;">{$mode}</td>
                </tr>
                <tr>
                    <td style="font-weight:bold; border-bottom:1px solid #e5e7eb;">Venue/Link</td>
                    <td style="border-bottom:1px solid #e5e7eb;">{$venue}</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Coordinator</td>
                    <td>{$coordinator}</td>
                </tr>
            </table>

            <p style="margin:0 0 16px; line-height:1.6;">
                All nominated faculty members are requested to attend the programme as per the schedule and ensure active participation.
                Your presence will be recorded in the CMS and performance tracking.
            </p>
            <p style="margin:0; line-height:1.6;">Regards,<br><strong>FDP Coordination Team</strong></p>
        </div>
    </div>
</body>
</html>
HTML;

        return ['subject' => $subject, 'body' => $body, 'htmlBody' => $htmlBody];
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

        $htmlBody = <<<HTML
<html>
<head>
    <meta charset="UTF-8">
    <title>{$subject}</title>
</head>
<body style="margin:0; padding:24px; background:#f4f6fb; font-family:Arial, sans-serif; color:#1f2937;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
        <div style="padding:24px 24px 12px; background:#7f1d1d; color:#ffffff; font-size:22px; font-weight:bold;">
            Defaulter Notice
        </div>
        <div style="padding:24px;">
            <p style="margin:0 0 16px; font-size:16px;">Dear {$name},</p>
            <p style="margin:0 0 16px; line-height:1.6;">
                It has been observed that you were marked absent for the Faculty Development Programme (FDP) on <strong>{$title}</strong>,
                conducted on <strong>{$date}</strong>.
            </p>

            <table cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse; margin:0 0 16px; background:#fff7ed; border:1px solid #fed7aa;">
                <tr>
                    <td style="font-weight:bold; width:35%; border-bottom:1px solid #fed7aa;">Title</td>
                    <td style="border-bottom:1px solid #fed7aa;">{$title}</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Date</td>
                    <td>{$date}</td>
                </tr>
            </table>

            <p style="margin:0 0 16px; line-height:1.6;">
                As participation in FDPs is an important academic and institutional requirement, your absence has been recorded in the CMS.
                If your absence was due to valid reasons (On Duty/Leave), you are requested to inform the FDP coordinator at the earliest for necessary updates.
                Kindly treat this as important.
            </p>
            <p style="margin:0; line-height:1.6;">Regards,<br><strong>FDP Coordination Team</strong></p>
        </div>
    </div>
</body>
</html>
HTML;

        return ['subject' => $subject, 'body' => $body, 'htmlBody' => $htmlBody];
    }
}
