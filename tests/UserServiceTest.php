<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\ValidationException;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\{PasswordService, UserService};
use App\Validators\UserValidator;
use PHPUnit\Framework\TestCase;

final class UserServiceTest extends TestCase
{
    private $userRepositoryMock;
    private $passwordServiceMock;
    private $userValidatorMock;
    private $userServiceMock;

    protected function setUp(): void
    {
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->passwordServiceMock = $this->createMock(PasswordService::class);
        $this->userValidatorMock = $this->createMock(UserValidator::class);

        $this->userServiceMock = new UserService(
            $this->userRepositoryMock,
            $this->userValidatorMock,
            $this->passwordServiceMock
        );
    }

    private function getValidUserData(): array
    {
        return [
            "firstname" => "Thiago",
            "lastname" => "Machado",
            "email" => "thiago.machado@email.com",
            "password" => "password123"
        ];
    }

    private function createUserInstance(): User
    {
        $user = new User("Thiago", "Machado", "thiago.machado@email.com");
        $user->setId(1);
        return $user;
    }

    public function testGetUsersEmpty(): void
    {
        $this->userRepositoryMock->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $this->assertSame([], $this->userServiceMock->getUsers());
    }

    public function testGetUsers(): void
    {
        $user = $this->createUserInstance();
        $expectedUsers = [$user->toArray()];

        $this->userRepositoryMock->expects($this->once())
            ->method('findAll')
            ->willReturn($expectedUsers);

        $users = $this->userServiceMock->getUsers();

        $this->assertCount(1, $users);
        $this->assertSame("Thiago", $users[0]['firstname']);
    }

    public function testCreateUserValidationFailure(): void
    {
        $this->expectException(ValidationException::class);

        $this->userValidatorMock->expects($this->once())
            ->method('validate')
            ->willThrowException(new ValidationException([]));

        $this->userServiceMock->createUser([]);
    }

    public function testCreateUser(): void
    {
        $userData = $this->getValidUserData();
        $hashedPassword = 'hashed_password';
        $expectedUser = $this->createUserInstance();

        $this->passwordServiceMock->expects($this->once())
            ->method('hashPassword')
            ->with($userData['password'])
            ->willReturn($hashedPassword);

        $this->userValidatorMock->expects($this->once())
            ->method('validate')
            ->with($userData, true);

        $this->userRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->callback(function (User $user) use ($userData, $hashedPassword): bool {
                return $user->getFirstname() === $userData['firstname'] &&
                    $user->getPassword() === $hashedPassword;
            }))
            ->willReturn($expectedUser);

        $result = $this->userServiceMock->createUser($userData);

        $this->assertSame($expectedUser, $result);
        $this->assertSame("Machado", $result->getLastname());
    }

    public function testFindUserNotFound(): void
    {
        $this->userRepositoryMock->expects($this->once())
            ->method('findById')
            ->with(1914)
            ->willReturn(null);

        $this->assertNull($this->userServiceMock->findUser(1914));
    }

    public function testFindUserSuccess(): void
    {
        $expectedUser = $this->createUserInstance();

        $this->userRepositoryMock->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($expectedUser);

        $result = $this->userServiceMock->findUser(1);
        $this->assertSame($expectedUser, $result);
    }

    public function testUpdateUserPartial(): void
    {
        $user = $this->createUserInstance();
        $updateData = ["firstname" => "Thiago2"];

        $this->userValidatorMock->expects($this->once())
            ->method('validate')
            ->with($updateData, false);

        $this->userRepositoryMock->expects($this->once())
            ->method('update')
            ->with($user, $updateData)
            ->willReturnCallback(function (User $user, array $data) {
                $user->setFirstname($data['firstname']);
                return $user;
            });

        $result = $this->userServiceMock->updateUser($user, $updateData);
        $this->assertSame("Thiago2", $result->getFirstname());
    }

    public function testDeleteUserSuccess(): void
    {
        $this->userRepositoryMock->expects($this->once())
            ->method('delete')
            ->with(1)
            ->willReturn(true);

        $this->assertTrue($this->userServiceMock->deleteUser(1));
    }

    public function testDeleteUserFailure(): void
    {
        $this->userRepositoryMock->expects($this->once())
            ->method('delete')
            ->with(999)
            ->willReturn(false);

        $this->assertFalse($this->userServiceMock->deleteUser(999));
    }
}
