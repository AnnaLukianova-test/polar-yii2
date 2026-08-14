<?php

namespace app\dto\polar;

class PolarTokenDto
{
    public function __construct(
        public readonly string $access_token,
        public readonly int $expires_in,
        public readonly int $polar_user_id,
    ) {
    }
}
