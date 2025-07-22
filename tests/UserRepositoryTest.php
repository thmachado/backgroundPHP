<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\CacheService;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;

final class UserRepositoryTest extends TestCase
{
    private $pdoMock;
    private $cacheServiceMock;
    private $userRepository;
    private string $cacheKey = "app:users";

    protected function setUp(): void
    {
        $this->pdoMock = $this->createMock(PDO::class);
        $this->cacheServiceMock = $this->createMock(CacheService::class);
        $this->userRepository = new UserRepository($this->pdoMock, $this->cacheServiceMock);
    }

    private function createUserInstance(): User
    {
        $user = new User("Thiago", "Machado", "thiago.machado@email.com");
        $user->setId(1);
        return $user;
    }

    public function testFindAllWithoutUsers(): void
    {
        $this->cacheServiceMock->expects($this->once())
            ->method('get')
            ->with($this->cacheKey)
            ->willReturn(null);

        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);

        $this->pdoMock->expects($this->once())
            ->method('query')
            ->with('SELECT id, firstname, lastname, email FROM users')
            ->willReturn($stmtMock);

        $users = $this->userRepository->findAll();

        $this->assertIsArray($users);
        $this->assertCount(0, $users);
        $this->assertSame([], $users);
    }

    public function testFindAllWithCachedUsers(): void
    {
        $cachedUsers = [
            [
                'id' => 1,
                'firstname' => 'Thiago',
                'lastname' => 'Machado',
                'email' => 'thiago.machado@email.com'
            ]
        ];

        $this->cacheServiceMock->expects($this->once())
            ->method('get')
            ->with($this->cacheKey)
            ->willReturn($cachedUsers);

        $this->pdoMock->expects($this->never())
            ->method('query');

        $users = $this->userRepository->findAll();

        $this->assertIsArray($users);
        $this->assertCount(1, $users);
        $this->assertSame('Thiago', $users[0]['firstname']);
    }

    public function testSaveUser(): void
    {
        $user = $this->createUserInstance();
        $user->setPassword('hashed_password');

        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO users (firstname, lastname, email, password) VALUES(:firstname, :lastname, :email, :password)')
            ->willReturn($stmtMock);

        $this->pdoMock->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('1');

        $this->cacheServiceMock->expects($this->once())
            ->method('del')
            ->with($this->cacheKey);

        $result = $this->userRepository->save($user);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame(1, $result->getId());
    }

    public function testUpdateUser(): void
    {
        $user = $this->createUserInstance();
        $updateData = ['firstname' => 'Thiago2', 'email' => 'new@email.com'];

        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('UPDATE users SET firstname = :firstname,email = :email WHERE id = :id')
            ->willReturn($stmtMock);

        $result = $this->userRepository->update($user, $updateData);
        $this->assertInstanceOf(User::class, $result);
        $this->assertSame('Thiago2', $result->getFirstname());
        $this->assertSame('new@email.com', $result->getEmail());
    }

    public function testDeleteUser(): void
    {
        $userid = 1;

        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmtMock->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM users WHERE id = :id')
            ->willReturn($stmtMock);

        $this->cacheServiceMock->expects($this->once())
            ->method('pipeline')
            ->willReturnCallback(function (callable $callback) {
                $callback();
            });

        $delExpectations = [
            ["{$this->cacheKey}:1"],
            [$this->cacheKey]
        ];

        $this->cacheServiceMock->expects($this->exactly(2))
            ->method('del')
            ->willReturnCallback(function (string $key) use (&$delExpectations) {
                $expectedArgs = array_shift($delExpectations);
                $this->assertSame($expectedArgs[0], $key);
                return true;
            });

        $result = $this->userRepository->delete($userid);
        $this->assertTrue($result);
        $this->assertEmpty($delExpectations, 'Not all expected del calls were made');
    }

    public function testFindByIdWithCache(): void
    {
        $userid = 1;
        $cachedUser = [
            'id' => $userid,
            'firstname' => 'Thiago',
            'lastname' => 'Machado',
            'email' => 'thiago.machado@email.com'
        ];

        $this->cacheServiceMock->expects($this->once())
            ->method('get')
            ->with("{$this->cacheKey}:1")
            ->willReturn($cachedUser);

        $this->pdoMock->expects($this->never())
            ->method('prepare');

        $result = $this->userRepository->findById($userid);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame('Thiago', $result->getFirstname());
    }

    public function testFindByIdWithoutCache(): void
    {
        $userid = 1;
        $this->cacheServiceMock->expects($this->once())
            ->method('get')
            ->with("{$this->cacheKey}:1")
            ->willReturn(null);

        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $stmtMock->expects($this->once())
            ->method('fetch')
            ->willReturn((object)[
                'id' => $userid,
                'firstname' => 'Thiago',
                'lastname' => 'Machado',
                'email' => 'thiago.machado@email.com'
            ]);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('SELECT id, firstname, lastname, email FROM users WHERE id = :id')
            ->willReturn($stmtMock);

        $this->cacheServiceMock->expects($this->once())
            ->method('set')
            ->with("{$this->cacheKey}:{$userid}", [
                'id' => $userid,
                'firstname' => 'Thiago',
                'lastname' => 'Machado',
                'email' => 'thiago.machado@email.com'
            ]);

        $result = $this->userRepository->findById($userid);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame('Machado', $result->getLastname());
    }

    public function testFindByIdNotFound(): void
    {
        $userid = 999;

        $this->cacheServiceMock->expects($this->once())
            ->method('get')
            ->with("{$this->cacheKey}:{$userid}")
            ->willReturn(null);

        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmtMock->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('SELECT id, firstname, lastname, email FROM users WHERE id = :id')
            ->willReturn($stmtMock);

        $this->cacheServiceMock->expects($this->never())
            ->method('set');

        $result = $this->userRepository->findById($userid);

        $this->assertNull($result);
    }
}
