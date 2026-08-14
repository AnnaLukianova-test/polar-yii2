<?php

namespace app\models\polar;

use app\models\User;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $user_id
 * @property int $polar_user_id
 * @property string $access_token
 * @property string $token_expires_at
 * @property string $member_id
 * @property string $connected_at
 * @property string|null $last_synced_at
 */
class PolarConnection extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%polar_connection}}';
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function isTokenExpired(): bool
    {
        return strtotime((string) $this->token_expires_at) <= time();
    }
}
