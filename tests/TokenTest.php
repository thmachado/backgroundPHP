<?php

declare(strict_types=1);

use App\Token;
use Firebase\JWT\{BeforeValidException, ExpiredException, JWT, Key, SignatureInvalidException};
use PHPUnit\Framework\TestCase;

final class TokenTest extends TestCase
{
    private Token $token;
    private string $bearer;
    private array $payload;
    private string $key = "Palmeiras";

    protected function setUp(): void
    {
        $this->payload = [
            "sub" => random_int(1, 99),
            "name" => "Palmeiras",
            "role" => "guest"
        ];
        $this->token = new Token($this->key);
        $this->bearer = $this->token->generateToken($this->payload);
    }

    public function testGenerateToken(): void
    {
        $this->assertIsString($this->bearer);
        $this->assertEquals($this->token->generateToken($this->payload), $this->bearer);
    }

    public function testDecodeToken(): void
    {
        $decoded = JWT::decode($this->bearer, new Key($this->token->getSecret(), "HS256"));
        $this->assertEquals($this->payload["sub"], $decoded->sub);
        $this->assertEquals($this->payload["name"], $decoded->name);
        $this->assertEquals($this->payload["role"], $decoded->role);
    }

    public function testInvalidKeyToken(): void
    {
        $this->expectException(SignatureInvalidException::class);
        JWT::decode($this->bearer, new Key('invalid', "HS256"));
    }

    public function testInvalidToken(): void
    {
        $this->expectException(UnexpectedValueException::class);
        JWT::decode('Palmeiras', new Key($this->token->getSecret(), "HS256"));
    }

    public function testExpiredToken(): void
    {
        $this->expectException(ExpiredException::class);
        $token = $this->token->generateToken(["exp" => time() - 3600]);
        JWT::decode($token, new Key($this->token->getSecret(), "HS256"));
    }

    public function testNotBeforeToken(): void
    {
        $this->expectException(BeforeValidException::class);
        $token = $this->token->generateToken(["nbf" => time() + 3600]);
        JWT::decode($token, new Key($this->token->getSecret(), "HS256"));
    }
}
