<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use App\Validators\UserValidator;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserValidator $validator,
        private PasswordService $passwordService
    ) {}

    public function getUsers(): array
    {
        return $this->userRepository->findAll();
    }

    public function createUser(array $data): User
    {
        $this->validator->validate($data, true);
        $passwordHash = $this->passwordService->hashPassword($data["password"]);
        return $this->userRepository->save(new User($data["firstname"], $data["lastname"], $data["email"], $passwordHash));
    }

    public function findUser(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    public function updateUser(User $user, array $data): User
    {
        $this->validator->validate($data, false);
        return $this->userRepository->update($user, $data);
    }

    public function deleteUser(int $id): bool
    {
        return $this->userRepository->delete($id);
    }
}
