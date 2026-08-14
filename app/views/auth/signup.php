<?php

use app\api\forms\SignupForm;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\web\View;

/** @var View $this */
/** @var SignupForm $model */

$this->title = 'Sign Up';
?>
<div class="auth-signup">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h3 mb-4 text-center"><?= Html::encode($this->title) ?></h1>

                    <?php $form = ActiveForm::begin([
                        'id' => 'signup-form',
                    ]); ?>

                    <?= $form->field($model, 'email')->textInput(['autofocus' => true, 'type' => 'email']) ?>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'first_name')->textInput() ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'last_name')->textInput() ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'password')->passwordInput() ?>
                    <?= $form->field($model, 'password_repeat')->passwordInput() ?>

                    <div class="form-group mt-4">
                        <?= Html::submitButton('Sign Up', ['class' => 'btn btn-primary w-100', 'name' => 'signup-button']) ?>
                    </div>

                    <p class="text-center text-muted mt-3 mb-0">
                        Already have an account?
                        <?= Html::a('Login', ['/auth/login']) ?>
                    </p>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
