<?php

declare(strict_types=1);

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
$dotenv->required(['POSTGRES_USER', 'POSTGRES_PASSWORD', 'POSTGRES_DB']);
