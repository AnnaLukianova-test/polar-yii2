<?php

namespace app\dto\polar;

class PolarTokenDto
{
    public function __construct(
        public readonly string $access_token,
        public readonly string $refresh_token,
        public readonly int $expires_in,
    ) {
    }
}
