<?php

return [
    'class' => yii\db\Connection::class,
    'dsn' => sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        getenv('DB_HOST') ?: 'db',
        getenv('DB_PORT') ?: '5432',
        getenv('DB_NAME') ?: 'polar_yii',
    ),
    'username' => getenv('DB_USER') ?: 'polar',
    'password' => getenv('DB_PASSWORD') ?: 'polar_secret',
    'charset' => 'utf8',
];
