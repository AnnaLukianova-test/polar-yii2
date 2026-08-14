<?php

namespace app\services\reg;

use app\services\reg\PasswordValidatorService;
use app\services\user\UserService;
use Yii;

class AuthService
{
    public function __construct(
        private UserService $users,
        private PasswordValidatorService $passwordValidator,
    ) {
    }

    public function login(string $email, string $password): bool
    {
        $user = $this->users->findByEmail($email);
        if ($user === null) {
            return false;
        }

        if (!$this->passwordValidator->validate($password, $user->password_hash)) {
            return false;
        }

        return Yii::$app->user->login($user);
    }

    public function logout(): void
    {
        Yii::$app->user->logout();
    }
}
