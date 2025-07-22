<?php

declare(strict_types=1);

use App\{Postgres, Token};

return [
    PDO::class => Postgres::getInstance(),
    Token::class => DI\create()->constructor(getenv("TOKEN"))
];
