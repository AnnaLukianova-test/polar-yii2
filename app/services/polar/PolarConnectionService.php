<?php

namespace app\services\polar;

use app\models\polar\PolarConnection;
use app\models\User;
use app\repositories\PolarConnectionRepository;
use app\repositories\PolarExerciseRepository;

class PolarConnectionService
{
    public function __construct(
        private PolarConnectionRepository $polarConnections,
        private PolarExerciseRepository $polarExercises,
    ) {
    }

    /**
     * Get the actual polar connection for the user. If the last connection is expired, return null.
     */
    public function getActualPolarConnection(User $user): ?PolarConnection
    {
        $lastConnection = $this->polarConnections->findLastByUserId($user->id);
        
        if ($lastConnection === null || $lastConnection->isTokenExpired()) {
            return null;
        }
        return $lastConnection;
    }

    public function countExercisesByUserId(int $userId): int
    {
        return $this->polarExercises->countByUserId($userId);
    }
}
