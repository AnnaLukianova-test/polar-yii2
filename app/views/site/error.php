<?php

use yii\helpers\Html;
use yii\web\View;

/** @var View $this */
?>
<div class="site-error">
    <h1><?= Html::encode($name) ?></h1>
    <div class="alert alert-danger">
        <?= nl2br(Html::encode($message)) ?>
    </div>
    <p>The above error occurred while the Web server was processing your request.</p>
    <p>Please contact us if you think this is a server error. Thank you.</p>
</div>
