<?php

namespace app\repositories;

use app\models\polar\PolarExercise;

class PolarExerciseRepository
{
    public function countByUserId(int $userId): int
    {
        return (int) PolarExercise::find()
            ->where(['user_id' => $userId])
            ->count();
    }

    /**
     * todo make DTOS
     * @return array<string, int> Y-m-d => training count
     */
    public function findTrainingDateCountsByUserId(int $userId): array
    {
        $rows = PolarExercise::find()
            ->select(['training_date', 'cnt' => 'COUNT(*)'])
            ->where(['user_id' => $userId])
            ->groupBy('training_date')
            ->asArray()
            ->all();

        $counts = [];
        foreach ($rows as $row) {
            $date = (string) $row['training_date'];
            if ($date === '') {
                continue;
            }
            $counts[$date] = (int) $row['cnt'];
        }

        return $counts;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function upsert(int $userId, string $polarExerciseId, array $payload): bool
    {
        $trainingDate = $this->extractTrainingDate($payload);
        $exercise = PolarExercise::findOne(['polar_exercise_id' => $polarExerciseId]);
        if ($exercise === null) {
            if ($trainingDate === null) {
                return false;
            }
            $exercise = new PolarExercise();
            $exercise->polar_exercise_id = $polarExerciseId;
            $exercise->training_date = $trainingDate;
        } elseif ($trainingDate !== null) {
            $exercise->training_date = $trainingDate;
        }

        $exercise->user_id = $userId;
        $exercise->payload = $payload;
        $exercise->synced_at = date('Y-m-d H:i:s');

        return $exercise->save(false);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractTrainingDate(array $payload): ?string
    {
        $raw = null;
        if (isset($payload['startTime']) && is_string($payload['startTime']) && $payload['startTime'] !== '') {
            $raw = $payload['startTime'];
        } elseif (
            isset($payload['start'])
            && is_array($payload['start'])
            && isset($payload['start']['dateTime'])
            && is_string($payload['start']['dateTime'])
            && $payload['start']['dateTime'] !== ''
        ) {
            $raw = $payload['start']['dateTime'];
        } elseif (isset($payload['start']) && is_string($payload['start']) && $payload['start'] !== '') {
            $raw = $payload['start'];
        }

        if ($raw === null) {
            return null;
        }

        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $raw, $matches) === 1) {
            return $matches[1];
        }

        $timestamp = strtotime($raw);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }
}
