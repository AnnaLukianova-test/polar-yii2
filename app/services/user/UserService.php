<?php

namespace app\services\user;

use app\dto\UserDto;
use app\models\User;
use app\repositories\UserRepository;

class UserService
{
    public function __construct(
        private UserRepository $users,
    ) {
    }

    public function findById(int $id): ?User
    {
        return $this->users->findById($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->users->findByEmail($email);
    }

    public function create(UserDto $dto): ?User
    {
        return $this->users->create($dto);
    }

    public function save(User $user): bool
    {
        return $this->users->save($user);
    }
}
