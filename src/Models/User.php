<?php

declare(strict_types=1);

namespace App\Models;

use InvalidArgumentException;
use RuntimeException;

class User
{
    private ?int $id = null;
    private string $firstname;
    private string $lastname;
    private string $email;
    private string $password;

    public function __construct(string $firstname, string $lastname, string $email, string $password = '')
    {
        $this->setFirstname($firstname);
        $this->setLastname($lastname);
        $this->setEmail($email);

        if (empty($password) === false) {
            $this->setPassword($password);
        }
    }

    public function setId(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException("ID must be a positive integer");
        }

        $this->id = $id;
    }

    public function setFirstname(string $firstname): void
    {
        if (empty($firstname)) {
            throw new InvalidArgumentException("Firstname can't be empty");
        }

        $this->firstname = $firstname;
    }

    public function setLastname(string $lastname): void
    {
        if (empty($lastname)) {
            throw new InvalidArgumentException("Firstname can't be empty");
        }

        $this->lastname = $lastname;
    }

    public function setEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email");
        }

        $this->email = $email;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getId(): int
    {
        if ($this->id === null) {
            throw new RuntimeException("ID is not set");
        }

        return $this->id;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'email' => $this->getEmail()
        ];
    }
}
