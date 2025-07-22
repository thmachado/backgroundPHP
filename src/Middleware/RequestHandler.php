<?php

declare(strict_types=1);

namespace App\Middleware;

use Closure;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandler implements RequestHandlerInterface
{
    public function __construct(private array $middlewares, private Closure $controllerHandler, private int $index = 0) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->index < count($this->middlewares)) {
            $middleware = $this->middlewares[$this->index];
            $this->index++;
            return $middleware->process($request, $this);
        }

        return ($this->controllerHandler)($request);
    }
}
