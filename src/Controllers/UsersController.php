<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Services\UserService;
use Exception;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

class UsersController
{
    public function __construct(private UserService $userService) {}

    public function index(): ResponseInterface
    {
        $users = $this->userService->getUsers();
        return new JsonResponse(["count" => count($users), "users" => $users], 200);
    }

    public function store(ServerRequestInterface $request): ResponseInterface
    {
        $data = json_decode($request->getBody()->getContents(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(["error" => ["code" => 422, "message" => "Invalid format (only json)"]], 422);
        }

        if (empty($data)) {
            return new JsonResponse(["error" => ["code" => 400, "message" => "No fields provided"]], 400);
        }

        try {
            $user = $this->userService->createUser($data);
            return new JsonResponse($user->toArray(), 201, ["Location" => "/users/" . $user->getId()]);
        } catch (ValidationException $e) {
            return new JsonResponse(["error" => ["code" => $e->getCode(), "message" => $e->getMessage(), "errors" => $e->getErrors()]], $e->getCode());
        } catch (Exception $e) {
            return new JsonResponse(["error" => ["code" => 500, "message" => "Server error"]], 500);
        }
    }

    public function show(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttributes()["id"];
        if (is_numeric($id) === false || (int) $id <= 0) {
            return new JsonResponse(["error" => ["code" => 400, "message" => "Invalid userid"]], status: 400);
        }

        try {
            $user = $this->userService->findUser((int) $id);
            if ($user === null) {
                return new JsonResponse(["error" => ["code" => 404, "message" => "User not found"]], 404);
            }

            return new JsonResponse($user->toArray(), 200);
        } catch (Exception $e) {
            return new JsonResponse(["error" => ["code" => 500, "message" => "Server error"]], 500);
        }
    }

    public function update(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttributes()["id"];
        if (is_numeric($id) === false || (int) $id <= 0) {
            return new JsonResponse(["error" => ["code" => 400, "message" => "Invalid userid"]], status: 400);
        }

        $data = json_decode($request->getBody()->getContents(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(["error" => ["code" => 422, "message" => "Invalid format (only json)"]], 422);
        }

        if (empty($data)) {
            return new JsonResponse(["error" => ["code" => 400, "message" => "No fields provided"]], 400);
        }

        try {
            $findUser = $this->userService->findUser((int) $id);
            if ($findUser === null) {
                return new JsonResponse(["error" => ["code" => 404, "message" => "User not found"]], 404);
            }

            $user = $this->userService->updateUser($findUser, $data);
            return new JsonResponse($user->toArray(), 200);
        } catch (Exception $e) {
            return new JsonResponse(["error" => ["code" => 500, "message" => $e->getMessage()]], 500);
        }
    }

    public function destroy(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttributes()["id"];
        if (is_numeric($id) === false || (int) $id <= 0) {
            return new JsonResponse(["error" => ["code" => 400, "message" => "Invalid userid"]], status: 400);
        }

        try {
            if ($this->userService->deleteUser((int) $id) === false) {
                return new JsonResponse(["error" => ["code" => 404, "message" => "User not found or already deleted"]], 404);
            }

            return new JsonResponse("", 204);
        } catch (Exception $e) {
            return new JsonResponse(["error" => ["code" => 500, "message" => $e->getMessage()]], 500);
        }
    }
}
