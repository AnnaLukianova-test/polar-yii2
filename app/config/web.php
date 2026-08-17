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
    'controllerNamespace' => 'app\\api\\controllers',
    'container' => [
        'singletons' => [
            app\repositories\UserRepository::class => app\repositories\UserRepository::class,
            app\services\user\UserService::class => app\services\user\UserService::class,
            app\services\reg\PasswordValidatorService::class => app\services\reg\PasswordValidatorService::class,
            app\services\reg\AuthService::class => app\services\reg\AuthService::class,
            app\services\reg\SignupService::class => app\services\reg\SignupService::class,
            app\services\profile\ProfileService::class => app\services\profile\ProfileService::class,
            app\services\polar\PolarConnectionService::class => app\services\polar\PolarConnectionService::class,
            app\repositories\PolarConnectionRepository::class => app\repositories\PolarConnectionRepository::class,
            app\repositories\PolarExerciseRepository::class => app\repositories\PolarExerciseRepository::class,
            app\services\polar\PolarAccessLinkClient::class => function () {
                return new app\services\polar\PolarAccessLinkClient(
                    getenv('POLAR_CLIENT_ID') ?: '',
                    getenv('POLAR_CLIENT_SECRET') ?: '',
                    getenv('POLAR_REDIRECT_URI') ?: '',
                    getenv('POLAR_AUTH_URL') ?: '',
                    getenv('POLAR_TOKEN_URL') ?: '',
                    getenv('POLAR_API_BASE_URL') ?: '',
                    new GuzzleHttp\Client(['timeout' => 30]),
                );
            },
            app\services\polar\PolarOAuthService::class => app\services\polar\PolarOAuthService::class,
            app\services\polar\PolarSyncService::class => app\services\polar\PolarSyncService::class,
            app\services\security\CipherService::class => function () {
                return new app\services\security\CipherService(
                    getenv('POLAR_TOKEN_ENCRYPTION_KEY') ?: getenv('COOKIE_VALIDATION_KEY') ?: '',
                );
            },
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
                'polar/connect' => 'polar/connect',
                'polar/callback' => 'polar/callback',
                'polar/sync' => 'polar/sync',
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
