<?php

use yii\db\Migration;

class m240814_120000_create_polar_tables extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%polar_connection}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->unique(),
            'polar_user_id' => $this->bigInteger()->notNull(),
            'access_token' => $this->text()->notNull(),
            'token_expires_at' => $this->timestamp()->notNull(),
            'member_id' => $this->string(64)->notNull(),
            'connected_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
            'last_synced_at' => $this->timestamp()->null(),
        ]);

        $this->addForeignKey(
            'fk_polar_connection_user',
            '{{%polar_connection}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE',
        );

        $this->createTable('{{%polar_exercise}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'polar_exercise_id' => $this->string(255)->notNull()->unique(),
            'payload' => 'jsonb NOT NULL',
            'synced_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
        ]);

        $this->addForeignKey(
            'fk_polar_exercise_user',
            '{{%polar_exercise}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE',
        );

        $this->createIndex('idx_polar_exercise_user_id', '{{%polar_exercise}}', 'user_id');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%polar_exercise}}');
        $this->dropTable('{{%polar_connection}}');
    }
}
