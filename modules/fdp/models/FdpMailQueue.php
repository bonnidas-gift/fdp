<?php

declare(strict_types=1);

namespace app\modules\fdp\models;

use Yii;
use yii\db\Expression;

class FdpMailQueue
{
    public static function tableName(): string
    {
        return '{{%fdp_mail_queue}}';
    }

    public static function queue(string $type, int $fdpId, string $toEmail, string $subject, string $body, string $htmlBody): bool
    {
        $exists = Yii::$app->db->createCommand(
            'SELECT id FROM ' . self::tableName() . ' WHERE type = :type AND fdp_id = :fdpId AND to_email = :toEmail AND subject = :subject AND status IN (\'pending\', \'sent\') LIMIT 1'
        )
            ->bindValues([
                ':type' => $type,
                ':fdpId' => $fdpId,
                ':toEmail' => strtolower(trim($toEmail)),
                ':subject' => $subject,
            ])
            ->queryScalar();

        if ($exists !== false && $exists !== null) {
            return false;
        }

        return (bool) Yii::$app->db->createCommand()->insert(self::tableName(), [
            'type' => $type,
            'fdp_id' => $fdpId,
            'to_email' => strtolower(trim($toEmail)),
            'subject' => $subject,
            'body' => $body,
            'html_body' => $htmlBody,
            'status' => 'pending',
            'attempts' => 0,
            'created_at' => new Expression('CURRENT_TIMESTAMP'),
        ])->execute();
    }

    public static function processPending(): int
    {
        $rows = Yii::$app->db->createCommand(
            'SELECT * FROM ' . self::tableName() . ' WHERE status = :status ORDER BY created_at ASC LIMIT 50'
        )->bindValue(':status', 'pending')->queryAll();

        $sent = 0;
        foreach ($rows as $row) {
            $toEmail = (string) ($row['to_email'] ?? '');
            $subject = (string) ($row['subject'] ?? '');
            $textBody = (string) ($row['body'] ?? '');
            $htmlBody = (string) ($row['html_body'] ?? '');

            if ($toEmail === '' || $subject === '') {
                Yii::$app->db->createCommand()->update(
                    self::tableName(),
                    ['status' => 'failed', 'error' => 'Missing recipient or subject', 'sent_at' => new Expression('CURRENT_TIMESTAMP')],
                    ['id' => (int) ($row['id'] ?? 0)]
                )->execute();
                continue;
            }

            try {
                $result = Yii::$app->mailer->compose()
                    ->setTo($toEmail)
                    ->setFrom([Yii::$app->params['senderEmail'] ?? 'noreply@example.com' => Yii::$app->params['senderName'] ?? 'FDP Team'])
                    ->setSubject($subject)
                    ->setTextBody($textBody)
                    ->setHtmlBody($htmlBody)
                    ->send();

                if ($result) {
                    Yii::$app->db->createCommand()->update(
                        self::tableName(),
                        ['status' => 'sent', 'attempts' => (int) ($row['attempts'] ?? 0) + 1, 'sent_at' => new Expression('CURRENT_TIMESTAMP')],
                        ['id' => (int) ($row['id'] ?? 0)]
                    )->execute();
                    $sent++;
                    continue;
                }

                Yii::$app->db->createCommand()->update(
                    self::tableName(),
                    ['status' => 'failed', 'attempts' => (int) ($row['attempts'] ?? 0) + 1, 'error' => 'Mailer send returned false', 'sent_at' => new Expression('CURRENT_TIMESTAMP')],
                    ['id' => (int) ($row['id'] ?? 0)]
                )->execute();
            } catch (\Throwable $e) {
                Yii::error($e->getMessage(), __METHOD__);
                Yii::$app->db->createCommand()->update(
                    self::tableName(),
                    ['status' => 'failed', 'attempts' => (int) ($row['attempts'] ?? 0) + 1, 'error' => $e->getMessage(), 'sent_at' => new Expression('CURRENT_TIMESTAMP')],
                    ['id' => (int) ($row['id'] ?? 0)]
                )->execute();
            }
        }

        return $sent;
    }
}
