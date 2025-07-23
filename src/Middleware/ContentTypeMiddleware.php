<?php

declare(strict_types=1);

namespace App\Middleware;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Psr\Http\Server\RequestHandlerInterface;

class ContentTypeMiddleware implements MiddlewareInterface
{
    public function __construct(
        private array $types = ["application/json"],
        private array $methods = ["POST", "PUT"]
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array($request->getMethod(), $this->methods) === false) {
            return new JsonResponse(["error" => ["code" => 400, "message" => "Method is not allowed"]], 400);
        }

        $header = strtok($request->getHeaderLine("Content-Type"), ";");
        $contentType = trim($header);
        if (empty($contentType)) {
            return new JsonResponse(["error" => ["code" => 400, "message" => "Content-Type header is required"]], 400);
        }

        if (in_array(strtolower($contentType), $this->types) === false) {
            return new JsonResponse(["error" => ["code" => 400, "message" => "application/json is required."]], 400);
        }

        return $handler->handle($request);
    }
}
