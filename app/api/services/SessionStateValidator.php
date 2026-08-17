<?php

namespace app\api\services;

use Yii;

class SessionStateValidator
{
    public const SESSION_STATE_KEY = 'polar_oauth_state';

    public static function validate(string $state, int $userId): bool
    {
        $stored = Yii::$app->session->get(self::SESSION_STATE_KEY);
        Yii::$app->session->remove(self::SESSION_STATE_KEY);

        if (!is_array($stored)) {
            return false;
        }

        return hash_equals((string) ($stored['state'] ?? ''), $state)
            && (int) ($stored['user_id'] ?? 0) === $userId;
    }
}
