<?php

namespace app\dto\polar;

class CreatePolarConnectionDto
{
    public function __construct(
        public readonly int $user_id,
        public readonly string $access_token,
        public readonly string $refresh_token,
        public readonly string $token_expires_at,
        public readonly string $member_id,
        public readonly ?int $polar_user_id = null,
    ) {
    }
}
