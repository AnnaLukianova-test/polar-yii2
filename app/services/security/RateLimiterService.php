<?php

namespace app\services\security;

use Yii;

class RateLimiterService
{
    public function __construct(
        private int $maxAttempts,
        private int $windowSeconds,
    ) {
    }

    public function isLimited(string $key): bool
    {
        return $this->getAttempts($key) >= $this->maxAttempts;
    }

    public function hit(string $key): void
    {
        $cacheKey = $this->cacheKey($key);
        $attempts = $this->getAttempts($key) + 1;

        Yii::$app->cache->set($cacheKey, $attempts, $this->windowSeconds);
    }

    public function reset(string $key): void
    {
        Yii::$app->cache->delete($this->cacheKey($key));
    }

    private function getAttempts(string $key): int
    {
        return (int) Yii::$app->cache->get($this->cacheKey($key));
    }

    private function cacheKey(string $key): string
    {
        return 'rate_limit:' . $key;
    }
}
