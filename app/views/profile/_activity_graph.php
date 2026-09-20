<?php

use yii\bootstrap5\Html;
use yii\web\View;

/** @var View $this */
/** @var array{weeks: list<list<array{date: string, count: int, level: int}>>, months: list<string>} $activityGraph */

$weekdayLabels = ['Mon', '', 'Wed', '', 'Fri', '', 'Sun'];
$fills = [
    0 => '#ebedf0',
    1 => '#9be9a8',
    2 => '#40c463',
    3 => '#30a14e',
    4 => '#216e39',
];
$weeks = $activityGraph['weeks'] ?? [];
$months = $activityGraph['months'] ?? [];
?>
<div class="activity-graph">
    <div class="activity-graph__scroll">
        <table class="activity-graph__table">
            <thead>
                <tr>
                    <td class="activity-graph__weekday"></td>
                    <?php foreach ($months as $month): ?>
                        <td class="activity-graph__month"><?= Html::encode($month) ?></td>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php for ($row = 0; $row < 7; $row++): ?>
                    <tr>
                        <td class="activity-graph__weekday"><?= Html::encode($weekdayLabels[$row]) ?></td>
                        <?php foreach ($weeks as $week): ?>
                            <?php
                            $day = $week[$row] ?? ['date' => '', 'count' => 0, 'level' => 0];
                            $count = (int) ($day['count'] ?? 0);
                            $level = (int) ($day['level'] ?? 0);
                            $fill = $fills[$level] ?? $fills[0];
                            $dateLabel = !empty($day['date'])
                                ? date('j M Y', strtotime((string) $day['date']))
                                : '';
                            $title = $count === 1
                                ? '1 training on ' . $dateLabel
                                : $count . ' trainings on ' . $dateLabel;
                            ?>
                            <td title="<?= Html::encode($title) ?>">
                                <svg class="activity-graph__day" width="11" height="11" viewBox="0 0 11 11" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <rect width="11" height="11" rx="2" fill="<?= Html::encode($fill) ?>"></rect>
                                </svg>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>
