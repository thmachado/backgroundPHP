<?php

declare(strict_types=1);

namespace App;

use Firebase\JWT\JWT;

class Token
{
    public function __construct(private string $secret) {}

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function generateToken(array $payload): string
    {
        return JWT::encode($payload, $this->secret, "HS256");
    }
}
