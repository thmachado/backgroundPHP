<?php

declare(strict_types=1);

use App\Models\User;
use PHPUnit\Framework\TestCase;

final class UserModelTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User("Thiago", "Machado", "thiago.machado@email.com");
        $this->user->setId(1);
    }

    public function testInitialConstrutorUser(): void
    {
        $this->assertSame([
            "id" => 1,
            "firstname" => "Thiago",
            "lastname" => "Machado",
            "email" => "thiago.machado@email.com"
        ], $this->user->toArray());
    }

    public function testGetAndSetId(): void
    {
        $this->user->setId(10);
        $this->assertSame(10, $this->user->getId());
    }

    public function testGetAndSetFirstname(): void
    {
        $this->user->setFirstname("Flávio");
        $this->assertSame("Flávio", $this->user->getFirstname());
    }

    public function testGetAndSetLastname(): void
    {
        $this->user->setLastname("Conceição");
        $this->assertSame("Conceição", $this->user->getLastname());
    }

    public function testGetAndSetEmail(): void
    {
        $this->user->setEmail("thiago.test@gmail.com");
        $this->assertSame("thiago.test@gmail.com", $this->user->getEmail());
    }

    public function testUserToArray(): void
    {
        $this->assertSame([
            "id" => $this->user->getId(),
            "firstname" => $this->user->getFirstname(),
            "lastname" => $this->user->getLastname(),
            "email" => $this->user->getEmail()
        ], $this->user->toArray());
    }

    public function testAllGetAndSetterAndToArray(): void
    {
        $user = new User("Roberto", "Falcão", "robertofalcao@email.com");
        $user->setId(1914);
        $user->setEmail("roberto@email.com");

        $this->assertSame([
            "id" => 1914,
            "firstname" => "Roberto",
            "lastname" => "Falcão",
            "email" => "roberto@email.com"
        ], $user->toArray());
    }

    public function testFirstnameEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new User("", "Machado", "thiagomachado@email.com");
    }

    public function testLastnameEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new User("Thiago", "", "thiagomachado@email.com");
    }

    public function testEmailEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new User("Thiago", "Machado", "");
    }

    public function testInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new User("Thiago", "Machado", "thiago");
    }

    public function testInvalidSetterFirstname(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $user = new User("Thiago", "Machado", "thiagomachado@email.com");
        $user->setFirstname("");
    }

    public function testInvalidSetterLastname(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $user = new User("Thiago", "Machado", "thiagomachado@email.com");
        $user->setLastname("");
    }

    public function testInvalidSetterEmailEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $user = new User("Thiago", "Machado", "thiagomachado@email.com");
        $user->setEmail("");
    }

    public function testInvalidSetterEmailFilter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $user = new User("Thiago", "Machado", "thiagomachado@email.com");
        $user->setEmail("thiago");
    }

    public function testInvalidGetId(): void
    {
        $this->expectException(RuntimeException::class);
        $user = new User("Thiago", "Machado", "thiagomachado@email.com");
        $user->getId();
    }

    public function testInvalidSetId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $user = new User("Thiago", "Machado", "thiagomachado@email.com");
        $user->setId(-1);
    }
}
