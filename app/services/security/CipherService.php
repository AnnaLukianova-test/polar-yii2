<?php

namespace app\services\security;

use RuntimeException;

class CipherService
{
    private const CIPHER = 'aes-256-gcm';
    private const TAG_LENGTH = 16;

    public function __construct(
        private string $key,
    ) {
    }

    public function encrypt(string $plaintext): string
    {
        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        if ($ivLength === false) {
            throw new RuntimeException('Unable to initialize cipher.');
        }

        $iv = random_bytes($ivLength);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $this->keyBytes(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH,
        );

        if ($ciphertext === false) {
            throw new RuntimeException('Unable to encrypt data.');
        }

        return base64_encode($iv . $tag . $ciphertext);
    }

    public function decrypt(string $payload): string
    {
        $decoded = base64_decode($payload, true);
        if ($decoded === false) {
            throw new RuntimeException('Invalid ciphertext.');
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        if ($ivLength === false || strlen($decoded) < $ivLength + self::TAG_LENGTH) {
            throw new RuntimeException('Invalid ciphertext.');
        }

        $iv = substr($decoded, 0, $ivLength);
        $tag = substr($decoded, $ivLength, self::TAG_LENGTH);
        $ciphertext = substr($decoded, $ivLength + self::TAG_LENGTH);

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $this->keyBytes(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
        );

        if ($plaintext === false) {
            throw new RuntimeException('Unable to decrypt data.');
        }

        return $plaintext;
    }

    private function keyBytes(): string
    {
        if ($this->key === '') {
            throw new RuntimeException('Encryption key is not configured.');
        }

        return hash('sha256', $this->key, true);
    }
}
