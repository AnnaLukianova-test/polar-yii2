<?php

namespace app\services\polar;

use app\models\polar\PolarConnection;
use app\models\User;
use app\repositories\PolarConnectionRepository;

class PolarConnectionService
{
    public function __construct(
        private PolarConnectionRepository $polarConnections,
    ) {
    }

    public function getPolarConnection(User $user): ?PolarConnection
    {
        return $this->polarConnections->findLastByUserId($user->id);
    }
}
