<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use App\Services\CacheService;
use PDO;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
        private CacheService $cacheService,
        private string $cacheKey = "app:users"
    ) {}

    public function findAll(): array
    {
        $cachedUsers = $this->cacheService->get($this->cacheKey);
        if ($cachedUsers) {
            return $cachedUsers;
        }

        $users = $this->pdo->query("SELECT id, firstname, lastname, email FROM users")->fetchAll();
        $userList = [];
        foreach ($users as $user) {
            $userClass = new User($user->firstname, $user->lastname, $user->email);
            $userClass->setId($user->id);
            $userList[] = $userClass->toArray();
        }

        if (empty($userList) === false) {
            $this->cacheService->set($this->cacheKey, $userList);
        }

        return $userList;
    }

    public function save(User $user): User
    {
        $stmt = $this->pdo->prepare("INSERT INTO users (firstname, lastname, email, password) VALUES(:firstname, :lastname, :email, :password)");
        $stmt->bindValue(":firstname", $user->getFirstname(), PDO::PARAM_STR);
        $stmt->bindValue(":lastname", $user->getLastname(), PDO::PARAM_STR);
        $stmt->bindValue(":email", $user->getEmail(), PDO::PARAM_STR);
        $stmt->bindValue(":password", $user->getPassword(), PDO::PARAM_STR);
        $stmt->execute();

        $user->setId((int) $this->pdo->lastInsertId());
        $this->cacheService->del($this->cacheKey);
        return $user;
    }

    public function update(User $user, array $data): User
    {
        $fields = ["firstname", "lastname", "email"];
        $updateFields = [];
        $bindings = [":id" => $user->getId()];

        foreach ($data as $key => $value) {
            if (in_array($key, $fields)) {
                $updateFields[] = "{$key} = :{$key}";
                $bindings[":{$key}"] = $value;

                $method = "set" . ucfirst($key);
                if (method_exists($user, $method)) {
                    $user->$method($value);
                }
            }
        }

        if (empty($updateFields)) {
            return $user;
        }

        $stmt = $this->pdo->prepare("UPDATE users SET " . implode(",", $updateFields) . " WHERE id = :id");
        $stmt->execute($bindings);

        $this->cacheService->del("{$this->cacheKey}:{$user->getId()}");
        $this->cacheService->del($this->cacheKey);
        return $user;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        $this->cacheService->pipeline(function () use ($id): void {
            $this->cacheService->del("{$this->cacheKey}:{$id}");
            $this->cacheService->del($this->cacheKey);
        });

        return $stmt->rowCount() > 0;
    }

    public function findById(int $id): ?User
    {
        $cachedUser = $this->cacheService->get("{$this->cacheKey}:{$id}");
        if ($cachedUser) {
            $user = new User($cachedUser["firstname"], $cachedUser["lastname"], $cachedUser["email"]);
            $user->setId($cachedUser["id"]);
            return $user;
        }

        $stmt = $this->pdo->prepare("SELECT id, firstname, lastname, email FROM users WHERE id = :id");
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_OBJ);
        if ($result === false) {
            return null;
        }

        $user = new User($result->firstname, $result->lastname, $result->email);
        $user->setId($result->id);

        $this->cacheService->set("{$this->cacheKey}:{$result->id}", $user->toArray());
        return $user;
    }
}
