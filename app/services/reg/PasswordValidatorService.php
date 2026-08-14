<?php

namespace app\services\reg;

use Yii;

class PasswordValidatorService
{
    public function validate(string $password, string $passwordHash): bool
    {
        return Yii::$app->security->validatePassword($password, $passwordHash);
    }

    public function hash(string $password): string
    {
        return Yii::$app->security->generatePasswordHash($password);
    }
}
