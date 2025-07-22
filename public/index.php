<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
$dotenv->required(['POSTGRES_USER', 'POSTGRES_PASSWORD', 'POSTGRES_DB']);

use App\Controllers\{TokenController, UsersController};
use App\Middleware\{ContentTypeMiddleware, JwtMiddleware, RateLimitMiddleware};
use App\Router;
use DI\ContainerBuilder;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\Diactoros\Response\JsonResponse;

header("Content-Type: application/json");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: no-referrer");
header("Content-Security-Policy: default-src 'self'");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header_remove("X-Powered-By");
header("Server");

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../config/container.php');
$container = $containerBuilder->build();

$request = ServerRequestFactory::fromGlobals();
$router = new Router($container);
$router->addGlobalMiddleware($container->get(RateLimitMiddleware::class));
$contentTypeMiddleware = $container->get(ContentTypeMiddleware::class);
$jwtMiddleware = $container->get(JwtMiddleware::class);

$router->get("/health", function () {
    return new JsonResponse(["status" => "Health check is ok!"], 200);
});

$router->get("/api/token", [TokenController::class, "index"]);
$router->get("/api/users", [UsersController::class, "index"], [$jwtMiddleware]);
$router->post("/api/users", [UsersController::class, "store"], [$jwtMiddleware, $contentTypeMiddleware]);
$router->get("/api/users/{id}", [UsersController::class, "show"], [$jwtMiddleware]);
$router->put("/api/users/{id}", [UsersController::class, "update"], [$jwtMiddleware, $contentTypeMiddleware]);
$router->delete("/api/users/{id}", [UsersController::class, "destroy"], [$jwtMiddleware]);

$response = $router->dispatch($request);
$emitter = new SapiEmitter();
$emitter->emit($response);
