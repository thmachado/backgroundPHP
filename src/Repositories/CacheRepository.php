<?php

declare(strict_types=1);

namespace App\Repositories;

use Exception;
use Predis\Client;

class CacheRepository
{
    public function __construct(private ?Client $client = null, private int $ttl = 60) {}

    public function get(string $key): ?array
    {
        if ($this->client === null) {
            return null;
        }

        try {
            $cachedKey = $this->client->get($key);
            if ($cachedKey) {
                return json_decode($cachedKey, true);
            }
        } catch (Exception $e) {
            error_log("Cache get error: " . $e->getMessage());
        }

        return null;
    }

    public function set(string $key, array|string $value): bool
    {
        if ($this->client === null) {
            return false;
        }

        try {
            $this->client->setex($key, $this->ttl, json_encode($value));
        } catch (Exception $e) {
            error_log(message: "Cache get error: " . $e->getMessage());
        }

        return true;
    }

    public function del(string $key): bool
    {
        if ($this->client === null) {
            return false;
        }

        try {
            $this->client->del($key);
        } catch (Exception $e) {
            error_log(message: "Cache error: " . $e->getMessage());
        }

        return true;
    }

    public function pipeline(callable $callback): void
    {
        if ($this->client === null) {
            return;
        }

        try {
            $this->client->pipeline($callback);
        } catch (Exception $e) {
            error_log("Cache pipeline error: " . $e->getMessage());
        }
    }
}
