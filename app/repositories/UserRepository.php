<?php

namespace app\repositories;

use app\dto\UserDto;
use app\models\User;

class UserRepository
{
    public function findById(int $id): ?User
    {
        return User::findOne($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::findOne(['email' => $email]);
    }

    public function create(UserDto $dto): ?User
    {
        $user = new User();
        $user->email = $dto->email;
        $user->first_name = $dto->first_name;
        $user->last_name = $dto->last_name;
        $user->password_hash = $dto->password_hash;

        return $user->save() ? $user : null;
    }

    public function save(User $user): bool
    {
        return $user->save();
    }
}
