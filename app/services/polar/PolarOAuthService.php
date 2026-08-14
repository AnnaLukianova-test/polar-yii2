<?php

namespace app\services\polar;

use app\api\services\OAuthStateValidator;
use app\api\services\PolarOAuthStateValidator;
use app\dto\polar\PolarTokenDto;
use Yii;

class PolarOAuthService
{
    public function __construct(
        private PolarAccessLinkClient $client,
    ) {
    }

    public function getAuthorizationUrl(int $userId): string
    {
        $state = Yii::$app->security->generateRandomString(32);
        Yii::$app->session->set(OAuthStateValidator::SESSION_STATE_KEY, [
            'state' => $state,
            'user_id' => $userId,
        ]);

        return $this->client->getAuthorizationUrl($state);
    }

    public function exchangeAuthorizationCode(string $code): PolarTokenDto
    {
        return $this->client->exchangeAuthorizationCode($code);
    }
}
