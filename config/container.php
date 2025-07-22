<?php

declare(strict_types=1);

use App\{Postgres, Redis, Token};
use App\Middleware\{ContentTypeMiddleware, RateLimitMiddleware};
use Predis\Client;

return [
    PDO::class => Postgres::getInstance(),
    Token::class => DI\create()->constructor(getenv("TOKEN")),
    Client::class => Redis::getClient(),
    RateLimitMiddleware::class => DI\create()->constructor(Redis::getClient()),
    ContentTypeMiddleware::class => DI\autowire()
];
