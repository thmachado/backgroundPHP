<?php

declare(strict_types=1);

use App\Redis;
use PHPUnit\Framework\TestCase;
use Predis\Client;

final class RedisTest extends TestCase
{
    private Client $client;
    private string $key = "app:tests";
    private int $ttl = 10;

    protected function setUp(): void
    {
        $this->client = Redis::getClient();
    }

    protected function tearDown(): void
    {
        $this->client->del($this->key);
    }

    public function testRedisConnection(): void
    {
        $this->assertInstanceOf(Client::class, $this->client);
    }

    public function testSetExAndGet(): void
    {
        $data = ["Palmeiras", "Sociedade Esportiva Palmeiras", "Verdão", "Porco"];
        $this->client->setEx($this->key, $this->ttl, json_encode($data));

        $dataRedis = $this->client->get($this->key);
        if ($dataRedis) {
            $dataRedis = json_decode($dataRedis);
            $this->assertSame($data, $dataRedis);
            $this->assertSame("Palmeiras", $dataRedis[0]);
        }

        $this->assertNotNull($dataRedis);
    }

    public function testTtlExpired(): void
    {
        $this->client->setEx($this->key, 1, "Palmeiras");
        sleep(2);
        $this->assertNull($this->client->get($this->key));
    }
}
