<?php

declare(strict_types=1);

use yii\db\Migration;

class m000000_000001_create_fdp_tables extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%fdp}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date()->notNull(),
            'time' => $this->string(100)->null(),
            'mode' => $this->string(50)->notNull()->defaultValue('Online'),
            'venue' => $this->string(255)->null(),
            'meeting_link' => $this->string(255)->null(),
            'coordinator_name' => $this->string(255)->notNull(),
            'reminder_sent_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createTable('{{%fdp_participant}}', [
            'id' => $this->primaryKey(),
            'fdp_id' => $this->integer()->notNull(),
            'faculty_name' => $this->string(255)->notNull(),
            'faculty_email' => $this->string(255)->notNull(),
            'department' => $this->string(255)->null(),
            'designation' => $this->string(255)->null(),
            'created_at' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createTable('{{%fdp_attendance}}', [
            'id' => $this->primaryKey(),
            'fdp_id' => $this->integer()->notNull(),
            'faculty_name' => $this->string(255)->notNull(),
            'faculty_email' => $this->string(255)->null(),
            'status' => $this->string(50)->notNull()->defaultValue('Present'),
            'notes' => $this->text()->null(),
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

        $this->addForeignKey(
            'fk_fdp_attendance_fdp',
            '{{%fdp_attendance}}',
            'fdp_id',
            '{{%fdp}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_fdp_attendance_fdp', '{{%fdp_attendance}}');
        $this->dropForeignKey('fk_fdp_participant_fdp', '{{%fdp_participant}}');
        $this->dropTable('{{%fdp_attendance}}');
        $this->dropTable('{{%fdp_participant}}');
        $this->dropTable('{{%fdp}}');
    }
}
