<?php

declare(strict_types=1);

use App\{Postgres, Redis, Token};
use App\Middleware\{ContentTypeMiddleware, RateLimitMiddleware, SecurityHeadersMiddleware};
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Services\PasswordService;
use App\Services\UserService;
use App\Validators\UserValidator;
use Predis\Client;

return [
    PDO::class => Postgres::getInstance(),
    UserRepositoryInterface::class => DI\autowire(UserRepository::class),
    UserValidator::class => DI\autowire(),
    UserService::class => DI\autowire(),
    Token::class => DI\create()->constructor(getenv("TOKEN")),
    Client::class => Redis::getClient(),
    RateLimitMiddleware::class => DI\create()->constructor(Redis::getClient()),
    ContentTypeMiddleware::class => DI\autowire(),
    SecurityHeadersMiddleware::class => DI\autowire(),
    PasswordService::class => DI\create()->constructor(getenv("PEPPER"))
];
