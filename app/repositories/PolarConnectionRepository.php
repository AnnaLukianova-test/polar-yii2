<?php

namespace app\repositories;

use app\dto\polar\CreatePolarConnectionDto;
use app\models\polar\PolarConnection;

class PolarConnectionRepository
{
    public function findLastByUserId(int $userId): ?PolarConnection
    {
        return PolarConnection::find()
        ->where(['user_id' => $userId])
        ->orderBy(['connected_at' => SORT_DESC])
        ->one();
    }

    public function create(CreatePolarConnectionDto $dto): ?PolarConnection
    {
        $connection = $this->findLastByUserId($dto->user_id) ?? new PolarConnection();
        $connection->user_id = $dto->user_id;
        $connection->polar_user_id = $dto->polar_user_id;
        $connection->access_token = $dto->access_token;
        $connection->token_expires_at = $dto->token_expires_at;
        $connection->member_id = $dto->member_id;
        if ($connection->connected_at === null) {
            $connection->connected_at = date('Y-m-d H:i:s');
        }

        return $connection->save() ? $connection : null;
    }

    public function save(PolarConnection $connection): bool
    {
        return $connection->save();
    }
}
