<?php

namespace app\repositories;

use app\models\polar\PolarExercise;

class PolarExerciseRepository
{
    /**
     * @param array<string, mixed> $payload
     */
    public function upsert(int $userId, string $polarExerciseId, array $payload): bool
    {
        $exercise = PolarExercise::findOne(['polar_exercise_id' => $polarExerciseId]);
        if ($exercise === null) {
            $exercise = new PolarExercise();
            $exercise->polar_exercise_id = $polarExerciseId;
        }

        $exercise->user_id = $userId;
        $exercise->payload = $payload;
        $exercise->synced_at = date('Y-m-d H:i:s');

        return $exercise->save(false);
    }
}
