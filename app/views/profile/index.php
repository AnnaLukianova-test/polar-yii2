<?php

use app\models\polar\PolarConnection;
use app\models\User;
use Yii;
use yii\bootstrap5\Html;
use yii\web\View;

/** @var View $this */
/** @var User $user */
/** @var PolarConnection|null $polarConnection */

$this->title = 'Profile';
?>
<div class="profile-index">
    <div class="jumbotron text-center bg-light p-5 rounded-3 mb-4">
        <h1 class="display-5">Hello, <?= Html::encode($user->getFullName()) ?>!</h1>
        <p class="lead text-muted mb-0"><?= Html::encode($user->email) ?></p>
    </div>

    <div class="text-center">
        <?php if ($polarConnection === null): ?>
            <?= Html::a('Connect My Polar App', ['/polar/connect'], [
                'class' => 'btn btn-primary btn-lg',
            ]) ?>
        <?php else: ?>
            <p class="text-muted mb-3">
                Polar connected
                <?php if ($polarConnection->last_synced_at): ?>
                    · last sync <?= Html::encode(Yii::$app->formatter->asDatetime($polarConnection->last_synced_at)) ?>
                <?php endif; ?>
            </p>
            <?= Html::a('Sync with My Polar App', ['/polar/sync'], [
                'class' => 'btn btn-primary btn-lg',
            ]) ?>
        <?php endif; ?>
    </div>
</div>
