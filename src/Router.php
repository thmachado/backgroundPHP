<?php

declare(strict_types=1);

namespace App;

use App\Middleware\{MiddlewareInterface, RequestHandler};
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

class Router
{
    public function __construct(
        private ContainerInterface $container,
        private array $routes = [],
        private array $globalMiddlewares = []
    ) {}

    public function addGlobalMiddleware(MiddlewareInterface $middleware): void
    {
        $this->globalMiddlewares[] = $middleware;
    }

    public function dispatch(ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        $path = $request->getUri()->getPath();

        if (isset($this->routes[$method]) === false) {
            return new JsonResponse(["error" => ["code" => 405, "message" => "Method not found"]], 405);
        }

        foreach ($this->routes[$method] as $route => $config) {
            $pattern = $this->convertToRegex($route);

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                $request = $this->addParamsToRequest($request, $route, $matches);

                $handler = $config["handler"];
                $middlewares = array_merge($this->globalMiddlewares, $config["middlewares"]);

                $controllerCallable = fn($req) => $this->handle($handler, $req);
                $handler = new RequestHandler($middlewares, $controllerCallable);
                return $handler->handle($request);
            }
        }

        return new JsonResponse(["error" => ["code" => 404, "message" => "Endpoint not found"]], 404);
    }

    private function convertToRegex(string $route): string
    {
        $pattern = preg_replace("/\{([a-z]+)\}/", "(?P<$1>[^\/]+)", $route);
        return '@^' . $pattern . '$@i';
    }

    private function addParamsToRequest(ServerRequestInterface $request, string $route, array $matches): ServerRequestInterface
    {
        preg_match_all("/\{([a-z]+)\}/i", $route, $paramNames);
        foreach ($paramNames[1] as $index => $name) {
            if (isset($matches[$index])) {
                $request = $request->withAttribute($name, $matches[$index]);
            }
        }

        return $request;
    }

    private function handle(array|callable $handler, ServerRequestInterface $request): mixed
    {
        if (is_callable($handler)) {
            return $handler($request);
        }

        [$class, $method] = $handler;
        if (class_exists($class) === false) {
            return new JsonResponse(["error" => ["code" => 404, "message" => "Class not found"]], 404);
        }

        $classInstance = $this->container->get($class);
        if (method_exists($classInstance, $method) === false) {
            return new JsonResponse(["error" => ["code" => 404, "message" => "Method not found"]], 404);
        }

        return $classInstance->$method($request);
    }

    private function registerRoute(string $method, string $path, array|callable $callback, array $middlewares = []): void
    {
        $this->routes[$method][$path] = ["handler" => $callback, "middlewares" => $middlewares];
    }

    public function get(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->registerRoute("GET", $path, $handler, $middlewares);
    }

    public function post(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->registerRoute("POST", $path, $handler, $middlewares);
    }

    public function put(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->registerRoute("PUT", $path, $handler, $middlewares);
    }

    public function delete(string $path, array|callable $handler, array $middlewares = []): void
    {
        $this->registerRoute("DELETE", $path, $handler, $middlewares);
    }
}
