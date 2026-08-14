<?php

use app\assets\AppAsset;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\web\View;

/** @var View $this */
/** @var string $content */

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header id="header">
    <?php
    $navItems = [];
    if (Yii::$app->user->isGuest) {
        $navItems[] = ['label' => 'Login', 'url' => ['/auth/login']];
        $navItems[] = ['label' => 'Sign Up', 'url' => ['/auth/signup']];
    } else {
        $navItems[] = ['label' => 'Profile', 'url' => ['/profile/index']];
        $navItems[] = '<li class="nav-item">'
            . Html::beginForm(['/auth/logout'], 'post', ['class' => 'd-inline'])
            . Html::submitButton(
                'Logout (' . Html::encode(Yii::$app->user->identity->getFullName()) . ')',
                ['class' => 'btn btn-link nav-link']
            )
            . Html::endForm()
            . '</li>';
    }

    NavBar::begin([
        'brandLabel' => 'Polar Yii',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => ['class' => 'navbar-expand-md navbar-dark bg-dark fixed-top'],
        'innerContainerOptions' => ['class' => 'container'],
    ]);

    echo Nav::widget([
        'options' => ['class' => 'navbar-nav ms-auto'],
        'encodeLabels' => false,
        'items' => $navItems,
    ]);

    NavBar::end();
    ?>
</header>

<main id="main" class="flex-shrink-0 mt-5 pt-4" role="main">
    <div class="container">
        <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
            <?php
            $cssType = $type === 'error' ? 'danger' : $type;
            $messages = is_array($message) ? $message : [$message];
            ?>
            <?php foreach ($messages as $flash): ?>
                <div class="alert alert-<?= Html::encode($cssType) ?>" role="alert">
                    <?= Html::encode((string) $flash) ?>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <?= $content ?>
    </div>
</main>

<footer id="footer" class="mt-auto py-3 bg-light">
    <div class="container">
        <p class="text-muted mb-0">&copy; Polar Yii <?= date('Y') ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
