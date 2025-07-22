<?php

declare(strict_types=1);

namespace App;

use Predis\{Client, ClientException};

class Redis
{
    private static ?Client $client = null;

    public static function getClient(): ?Client
    {
        if (self::$client === null) {
            try {
                self::$client = new Client([
                    'host' => getenv("REDIS_HOST"),
                    'port' => getenv("REDIS_PORT")
                ]);
            } catch (ClientException $e) {
                error_log("Redis failed: " . $e->getMessage());
                self::$client = null;
            }
        }

        return self::$client;
    }
}
