<?php

namespace app\dto;

class UserDto
{
    public function __construct(
        public readonly string $email,
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly string $password_hash,
    ) {
    }
}
