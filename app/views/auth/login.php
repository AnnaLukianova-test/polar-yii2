<?php

use app\api\forms\LoginForm;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\web\View;

/** @var View $this */
/** @var LoginForm $model */

$this->title = 'Login';
?>
<div class="auth-login">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h3 mb-4 text-center"><?= Html::encode($this->title) ?></h1>

                    <?php $form = ActiveForm::begin([
                        'id' => 'login-form',
                    ]); ?>

                    <?= $form->field($model, 'email')->textInput(['autofocus' => true, 'type' => 'email']) ?>
                    <?= $form->field($model, 'password')->passwordInput() ?>

                    <div class="form-group mt-4">
                        <?= Html::submitButton('Login', ['class' => 'btn btn-primary w-100', 'name' => 'login-button']) ?>
                    </div>

                    <p class="text-center text-muted mt-3 mb-0">
                        Don't have an account?
                        <?= Html::a('Sign Up', ['/auth/signup']) ?>
                    </p>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
