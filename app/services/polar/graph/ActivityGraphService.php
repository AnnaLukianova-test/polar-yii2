<?php

namespace app\services\polar\graph;

use app\repositories\PolarExerciseRepository;
use DateInterval;
use DateTimeImmutable;

class ActivityGraphService
{
    public function __construct(
        private PolarExerciseRepository $polarExercises,
    ) {
    }

    /**
     * GitHub-style 53-week Monday–Sunday activity grid ending this week.
     *
     * @return array{
     *     weeks: list<list<array{date: string, count: int, level: int}>>,
     *     months: list<string>
     * }
     */
    public function getLastYearWeeklyGroupedTrainingsCount(int $userId): array
    {
        $counts = $this->polarExercises->findTrainingDateCountsByUserId($userId);

        $today = new DateTimeImmutable('today');
        $mondayThisWeek = $today->sub(new DateInterval('P' . ((int) $today->format('N') - 1) . 'D'));
        $cursor = $mondayThisWeek->sub(new DateInterval('P52W'));

        $weeks = [];
        $months = [];
        $previousMonthKey = null;

        for ($weekIndex = 0; $weekIndex < 53; $weekIndex++) {
            $week = [];
            $monthLabel = '';
            for ($dayIndex = 0; $dayIndex < 7; $dayIndex++) {
                $date = $cursor->format('Y-m-d');
                $count = $counts[$date] ?? 0;
                $week[] = [
                    'date' => $date,
                    'count' => $count,
                    'level' => min(4, $count),
                ];

                $monthKey = $cursor->format('Y-m');
                if ($monthKey !== $previousMonthKey) {
                    $monthLabel = $cursor->format('M');
                    $previousMonthKey = $monthKey;
                }

                $cursor = $cursor->modify('+1 day');
            }
            $weeks[] = $week;
            $months[] = $monthLabel;
        }

        return [
            'weeks' => $weeks,
            'months' => $months,
        ];
    }
}
