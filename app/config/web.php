<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'polar-yii',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'homeUrl' => ['/profile/index'],
    'container' => [
        'singletons' => [
            app\repositories\UserRepository::class => app\repositories\UserRepository::class,
            app\services\user\UserService::class => function ($container) {
                return new app\services\user\UserService(
                    $container->get(app\repositories\UserRepository::class),
                );
            },
            app\services\reg\PasswordValidatorService::class => app\services\reg\PasswordValidatorService::class,
            app\services\reg\AuthService::class => function ($container) {
                return new app\services\reg\AuthService(
                    $container->get(app\services\user\UserService::class),
                    $container->get(app\services\reg\PasswordValidatorService::class),
                );
            },
            app\services\reg\SignupService::class => function ($container) {
                return new app\services\reg\SignupService(
                    $container->get(app\services\user\UserService::class),
                    $container->get(app\services\reg\PasswordValidatorService::class),
                );
            },
            app\services\user\ProfileService::class => function ($container) {
                return new app\services\user\ProfileService(
                    $container->get(app\services\user\UserService::class),
                );
            },
            app\services\PolarSyncService::class => app\services\PolarSyncService::class,
            app\services\security\RateLimiterService::class => function () {
                return new app\services\security\RateLimiterService(
                    (int) (getenv('RATE_LIMIT_MAX_ATTEMPTS') ?: 5),
                    (int) (getenv('RATE_LIMIT_WINDOW') ?: 900),
                );
            },
        ],
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => getenv('COOKIE_VALIDATION_KEY') ?: '',
        ],
        'cache' => [
            'class' => yii\caching\FileCache::class,
        ],
        'user' => [
            'identityClass' => app\models\User::class,
            'enableAutoLogin' => false,
            'loginUrl' => ['auth/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'profile/index',
                'login' => 'auth/login',
                'signup' => 'auth/signup',
                'logout' => 'auth/logout',
                'profile' => 'profile/index',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => yii\gii\Module::class,
        'allowedIPs' => ['*'],
    ];
}

return $config;
