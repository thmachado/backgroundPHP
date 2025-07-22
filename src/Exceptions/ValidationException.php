<?php

declare(strict_types=1);

namespace App\Exceptions;

class ValidationException extends \RuntimeException
{
    public function __construct(private array $errors, string $message = 'Validation failed', int $code = 422)
    {
        parent::__construct($message, $code);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
