<?php

use app\models\User;
use yii\bootstrap5\Html;
use yii\web\View;

/** @var View $this */
/** @var User $user */

$this->title = 'Profile';
?>
<div class="profile-index">
    <div class="jumbotron text-center bg-light p-5 rounded-3 mb-4">
        <h1 class="display-5">Hello, <?= Html::encode($user->getFullName()) ?>!</h1>
        <p class="lead text-muted mb-0"><?= Html::encode($user->email) ?></p>
    </div>

    <div class="text-center">
        <?= Html::button('Sync with My Polar App', [
            'class' => 'btn btn-primary btn-lg',
            'disabled' => true,
            'title' => 'Coming soon',
        ]) ?>
    </div>
</div>
