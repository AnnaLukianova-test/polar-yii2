<?php

namespace app\exceptions\polar;

use RuntimeException;

class PolarApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function isConflict(): bool
    {
        return $this->statusCode === 409;
    }
}
