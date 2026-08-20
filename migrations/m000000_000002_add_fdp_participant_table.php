<?php

declare(strict_types=1);

use yii\db\Migration;

class m000000_000002_add_fdp_participant_table extends Migration
{
    public function safeUp(): void
    {
        if (!$this->db->getTableSchema('{{%fdp}}', true)) {
            throw new \RuntimeException('Base FDP table is missing. Please run the initial FDP migration first.');
        }

        if (!$this->db->getTableSchema('{{%fdp_participant}}', true)) {
            $this->createTable('{{%fdp_participant}}', [
                'id' => $this->primaryKey(),
                'fdp_id' => $this->integer()->notNull(),
                'faculty_name' => $this->string(255)->notNull(),
                'faculty_email' => $this->string(255)->notNull(),
                'department' => $this->string(255)->null(),
                'designation' => $this->string(255)->null(),
                'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            ]);

            $this->addForeignKey(
                'fk_fdp_participant_fdp',
                '{{%fdp_participant}}',
                'fdp_id',
                '{{%fdp}}',
                'id',
                'CASCADE',
                'CASCADE'
            );
        }

        if (!$this->db->getTableSchema('{{%fdp}}', true)->getColumn('reminder_sent_at')) {
            $this->addColumn('{{%fdp}}', 'reminder_sent_at', $this->dateTime()->null());
        }
    }

    public function safeDown(): void
    {
        if ($this->db->getTableSchema('{{%fdp_participant}}', true)) {
            $this->dropForeignKey('fk_fdp_participant_fdp', '{{%fdp_participant}}');
            $this->dropTable('{{%fdp_participant}}');
        }

        if ($this->db->getTableSchema('{{%fdp}}', true) && $this->db->getTableSchema('{{%fdp}}')->getColumn('reminder_sent_at')) {
            $this->dropColumn('{{%fdp}}', 'reminder_sent_at');
        }
    }
}
