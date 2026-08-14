<?php

namespace app\models\polar;

use app\models\User;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property string $polar_exercise_id
 * @property array|string $payload
 * @property string $synced_at
 */
class PolarExercise extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%polar_exercise}}';
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (is_array($this->payload)) {
            $this->payload = json_encode($this->payload, JSON_UNESCAPED_UNICODE);
        }

        return true;
    }

    public function afterFind(): void
    {
        parent::afterFind();
        if (is_string($this->payload)) {
            $decoded = json_decode($this->payload, true);
            if (is_array($decoded)) {
                $this->payload = $decoded;
            }
        }
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
