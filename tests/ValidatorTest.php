<?php

declare(strict_types=1);

use App\Exceptions\ValidationException;
use App\Validators\UserValidator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    private UserValidator $userValidator;

    protected function setUp(): void
    {
        $this->userValidator = new UserValidator();
    }

    public function testInvalidRequiredData(): void
    {
        $this->expectException(ValidationException::class);
        $this->userValidator->validate(["lastname" => "Machado", "email" => "thiago.machado@email.com"], true);
    }

    public function testInvalidEmailData(): void
    {
        $this->expectException(ValidationException::class);
        $this->userValidator->validate([
            "firstname" => "Thiago",
            "lastname" => "Machado",
            "email" => "thiago",
            "password" => "thiago"
        ], true);
    }

    public function testInvalidStringData(): void
    {
        $this->expectException(ValidationException::class);
        $this->userValidator->validate([
            "firstname" => 1914,
            "lastname" => "Machado",
            "email" => "thiago.machado@email.com",
            "password" => "thiago"
        ], true);
    }

    public function testEmptyString(): void
    {
        $this->expectException(ValidationException::class);
        $this->userValidator->validate(
            [
                "firstname" => "",
                "lastname" => "Machado",
                "email" => "thiago.machado@email.com",
                "password" => "thiago"
            ],
            true
        );
    }
    public function testInvalidStrlenString(): void
    {
        $this->expectException(ValidationException::class);
        $this->userValidator->validate([
            "firstname" => "Thiago Machado da Silva dos Santos da Conceição do Amazonas de São Paulo do Rio de Janeiro de Minas Gerais",
            "lastname" => "Machado",
            "email" => "thiago.machado@email.com",
            "password" => "thiago"
        ], true);
    }

    public function testMultipleInvalidFields(): void
    {
        $this->expectException(ValidationException::class);
        $this->userValidator->validate([
            "firstname" => "Thiago Machado da Silva dos Santos da Conceição do Amazonas de São Paulo do Rio de Janeiro de Minas Gerais",
            "lastname" => "",
            "email" => "thiago",
            "password" => "thiago"
        ], true);
    }

    public function testValidData(): void
    {
        $this->userValidator->validate([
            "firstname" => "Thiago",
            "lastname" => "Machado",
            "email" => "thiago.machado@email.com",
            "password" => "thiago"
        ], true);

        $this->addToAssertionCount(1);
    }

    public function testInvalidPartialData(): void
    {
        $this->expectException(ValidationException::class);
        $this->userValidator->validate(["email" => "thiago"], false);
    }

    public function testPartialData(): void
    {
        $this->userValidator->validate(["firstname" => "Thiago", "email" => "thiago.machado@email.com"], false);
        $this->addToAssertionCount(1);
    }
}
