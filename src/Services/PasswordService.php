<?php

declare(strict_types=1);

namespace App\Services;

class PasswordService
{
    public function __construct(private string $pepper) {}

    public function hashPassword(string $password): string
    {
        $peppered = hash_hmac('sha256', $password, $this->pepper);
        return password_hash($peppered, PASSWORD_ARGON2ID);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        $peppered = hash_hmac('sha256', $password, $this->pepper);
        return password_verify($peppered, $hash);
    }
}
