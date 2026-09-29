<?php

namespace app\api\services;

use app\models\User;
use app\services\user\UserService;
use Yii;

class ProfileService
{
    public function __construct(
        private UserService $users,
    ) {
    }

    public function getCurrentUser(): ?User
    {
        $identity = Yii::$app->user->identity;
        if ($identity === null) {
            return null;
        }

        return $this->users->findById($identity->getId());
    }
}
