<?php

use app\models\polar\PolarExercise;
use Yii;
use yii\bootstrap5\Html;
use yii\web\View;

/** @var View $this */
/** @var list<PolarExercise> $trainings */

$this->title = 'Trainings list';
?>
<div class="trainings-list">
    <h1 class="h3 mb-4"><?= Html::encode($this->title) ?></h1>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Training date</th>
                    <th scope="col">Comment</th>
                    <th scope="col">Discomfort degree</th>
                    <th scope="col">Foot Problem</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($trainings === []): ?>
                    <tr>
                        <td colspan="5" class="text-muted">No trainings yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($trainings as $training): ?>
                        <tr>
                            <td><?= (int) $training->id ?></td>
                            <td><?= Html::encode(Yii::$app->formatter->asDate($training->training_date)) ?></td>
                            <td>
                                <input type="text" class="form-control" aria-label="Comment">
                            </td>
                            <td class="trainings-list__discomfort">
                                <?= Html::dropDownList(null, 0, range(0, 5), [
                                    'class' => 'form-select',
                                    'aria-label' => 'Discomfort degree',
                                ]) ?>
                            </td>
                            <td class="trainings-list__foot-problem">
                                <input type="checkbox" class="form-check-input" aria-label="Foot Problem">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
