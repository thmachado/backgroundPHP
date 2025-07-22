<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
$dotenv->required(['POSTGRES_USER', 'POSTGRES_PASSWORD', 'POSTGRES_DB']);

use App\Controllers\TokenController;
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

$router->get("/health", function () {
    return new JsonResponse(["status" => "Health check is ok!"], 200);
});
$router->get("/api/token", [TokenController::class, "index"]);
$response = $router->dispatch($request);
$emitter = new SapiEmitter();
$emitter->emit($response);
