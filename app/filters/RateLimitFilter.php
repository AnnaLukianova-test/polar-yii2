<?php

namespace app\filters;

use app\services\security\RateLimiterService;
use Yii;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\web\Request;
use yii\web\TooManyRequestsHttpException;

class RateLimitFilter extends ActionFilter
{
    public string $scope = 'auth';

    public function __construct(
        private RateLimiterService $rateLimiter,
        $config = [],
    ) {
        parent::__construct($config);
    }

    /**
     * @param Action $action
     */
    public function beforeAction($action): bool
    {
        /** @var Request $request */
        $request = Yii::$app->request;

        if (!$request->isPost) {
            return parent::beforeAction($action);
        }

        $key = $this->buildKey($action->id, $request->userIP);

        if ($this->rateLimiter->isLimited($key)) {
            throw new TooManyRequestsHttpException(
                'Too many attempts. Please try again later.',
            );
        }

        return parent::beforeAction($action);
    }

    public function buildKey(string $actionId, ?string $ip): string
    {
        return $this->scope . ':' . $actionId . ':' . ($ip ?? 'unknown');
    }
}
