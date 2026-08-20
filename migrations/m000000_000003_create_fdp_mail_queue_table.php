<?php

declare(strict_types=1);

use yii\db\Migration;

class m000000_000003_create_fdp_mail_queue_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%fdp_mail_queue}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string(50)->notNull(),
            'fdp_id' => $this->integer()->notNull(),
            'to_email' => $this->string(255)->notNull(),
            'subject' => $this->string(255)->notNull(),
            'body' => $this->text()->notNull(),
            'html_body' => $this->text()->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('pending'),
            'attempts' => $this->integer()->notNull()->defaultValue(0),
            'error' => $this->text()->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'sent_at' => $this->dateTime()->null(),
        ]);

        $this->createIndex('idx_fdp_mail_queue_status_created', '{{%fdp_mail_queue}}', ['status', 'created_at']);
        $this->createIndex('idx_fdp_mail_queue_fdp_id', '{{%fdp_mail_queue}}', ['fdp_id']);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%fdp_mail_queue}}');
    }
}
