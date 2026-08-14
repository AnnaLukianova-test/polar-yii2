<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property int $id
 * @property string $email
 * @property string $first_name
 * @property string $last_name
 * @property string $password_hash
 * @property string $registered_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName(): string
    {
        return '{{%user}}';
    }

    public function rules(): array
    {
        return [
            [['email', 'first_name', 'last_name', 'password_hash'], 'required'],
            [['registered_at'], 'safe'],
            [['email'], 'email'],
            [['email'], 'unique'],
            [['first_name', 'last_name'], 'string', 'max' => 100],
            [['password_hash'], 'string', 'max' => 255],
        ];
    }

    public static function findIdentity($id): ?self
    {
        //todo remove when change auth
        return self::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?self
    {
        return null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAuthKey(): ?string
    {
        return null;
    }

    public function validateAuthKey($authKey): bool
    {
        return false;
    }

    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
