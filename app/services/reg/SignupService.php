<?php

namespace app\services\reg;

use app\dto\UserDto;
use app\api\forms\SignupForm;
use app\models\User;
use app\services\user\UserService;
use Yii;

class SignupService
{
    public function __construct(
        private UserService $users,
        private PasswordValidatorService $passwordValidator,
    ) {
    }

    public function register(SignupForm $form): ?User
    {
        $dto = new UserDto(
            email: $form->email,
            first_name: $form->first_name,
            last_name: $form->last_name,
            password_hash: $this->passwordValidator->hash($form->password),
        );

        $user = $this->users->create($dto);
        if ($user === null) {
            return null;
        }

        Yii::$app->user->login($user);

        return $user;
    }
}
