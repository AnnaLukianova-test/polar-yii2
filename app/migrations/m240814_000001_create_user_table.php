<?php

use yii\db\Migration;

class m240814_000001_create_user_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'email' => $this->string(255)->notNull()->unique(),
            'first_name' => $this->string(100)->notNull(),
            'last_name' => $this->string(100)->notNull(),
            'password_hash' => $this->string(255)->notNull(),
            'registered_at' => $this->timestamp()->notNull()->defaultExpression('NOW()'),
        ]);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%user}}');
    }
}
