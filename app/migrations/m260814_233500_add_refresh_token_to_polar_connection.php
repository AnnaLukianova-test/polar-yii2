<?php

use yii\db\Migration;

class m260814_233500_add_refresh_token_to_polar_connection extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%polar_connection}}', 'refresh_token', $this->text()->null());
        $this->alterColumn('{{%polar_connection}}', 'polar_user_id', $this->bigInteger()->null());
    }

    public function safeDown(): void
    {
        $this->alterColumn('{{%polar_connection}}', 'polar_user_id', $this->bigInteger()->notNull());
        $this->dropColumn('{{%polar_connection}}', 'refresh_token');
    }
}
