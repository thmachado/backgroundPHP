<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap/app.php';

use App\Controllers\{TokenController, UsersController};
use App\Middleware\{ContentTypeMiddleware, JwtMiddleware, RateLimitMiddleware, SecurityHeadersMiddleware};
use App\Router;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\Diactoros\Response\JsonResponse;

$container = require __DIR__ . '/../bootstrap/container.php';

$router = new Router($container);
$router->addGlobalMiddleware($container->get(SecurityHeadersMiddleware::class));
$router->addGlobalMiddleware($container->get(RateLimitMiddleware::class));
$contentTypeMiddleware = $container->get(ContentTypeMiddleware::class);
$jwtMiddleware = $container->get(JwtMiddleware::class);

$router->get("/health", function (): JsonResponse {
    return new JsonResponse(["status" => "Health check is ok!"], 200);
});

$router->get("/api/token", [TokenController::class, "index"]);
$router->get("/api/users", [UsersController::class, "index"], [$jwtMiddleware]);
$router->post("/api/users", [UsersController::class, "store"], [$jwtMiddleware, $contentTypeMiddleware]);
$router->get("/api/users/{id}", [UsersController::class, "show"], [$jwtMiddleware]);
$router->put("/api/users/{id}", [UsersController::class, "update"], [$jwtMiddleware, $contentTypeMiddleware]);
$router->delete("/api/users/{id}", [UsersController::class, "destroy"], [$jwtMiddleware]);

$response = $router->dispatch(ServerRequestFactory::fromGlobals());
$emitter = new SapiEmitter();
$emitter->emit($response);