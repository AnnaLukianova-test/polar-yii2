<?php

use yii\db\Migration;

class m260920_201800_add_training_date_to_polar_exercise extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%polar_exercise}}', 'training_date', $this->date()->null());

        $this->execute(<<<'SQL'
UPDATE {{%polar_exercise}}
SET training_date = COALESCE(
    substring(payload->>'startTime' from '^\d{4}-\d{2}-\d{2}'),
    CASE
        WHEN jsonb_typeof(payload->'start') = 'object'
        THEN substring(payload->'start'->>'dateTime' from '^\d{4}-\d{2}-\d{2}')
        WHEN jsonb_typeof(payload->'start') = 'string'
        THEN substring(payload->>'start' from '^\d{4}-\d{2}-\d{2}')
        ELSE NULL
    END,
    (synced_at)::date::text
)::date
WHERE training_date IS NULL
SQL);

        $this->alterColumn('{{%polar_exercise}}', 'training_date', $this->date()->notNull());
    }

    public function safeDown(): void
    {
        $this->dropColumn('{{%polar_exercise}}', 'training_date');
    }
}
