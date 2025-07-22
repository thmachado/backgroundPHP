<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

final class ApiTest extends TestCase
{
    private Client $client;
    private string $token;

    protected function setUp(): void
    {
        $this->client = new Client(["base_uri" => "http://localhost:80", "http_errors" => false]);
        $response = $this->client->get("/api/token");
        $tokenJSON = json_decode((string) $response->getBody());
        $this->token = $tokenJSON->token;
    }

    public function testApiIndex(): void
    {
        $response = $this->client->get("/api/users", [
            "headers" => ["Authorization" => "Bearer {$this->token}"]
        ]);

        $body = json_decode((string) $response->getBody(), true);

        $this->assertIsArray($body);
        $this->assertArrayHasKey("count", $body);
        $this->assertArrayHasKey("users", $body);
        $this->assertIsArray($body["users"]);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testApiStoreJsonFailed(): void
    {
        $response = $this->client->post("/api/users", [
            "headers" => ["Authorization" => "Bearer {$this->token}", "Content-Type" => "application/json"],
            ""
        ]);

        $body = json_decode((string) $response->getBody(), true);

        $this->assertIsArray($body);
        $this->assertArrayHasKey("error", $body);
        $this->assertArrayHasKey("code", $body["error"]);
        $this->assertArrayHasKey("message", $body["error"]);
        $this->assertSame(422, $body["error"]["code"]);
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testApiStoreJsonEmpty(): void
    {
        $response = $this->client->post("/api/users", [
            "headers" => ["Authorization" => "Bearer {$this->token}", "Content-Type" => "application/json"],
            "json" => []
        ]);

        $body = json_decode((string) $response->getBody(), true);

        $this->assertIsArray($body);
        $this->assertArrayHasKey("error", $body);
        $this->assertArrayHasKey("code", $body["error"]);
        $this->assertArrayHasKey("message", $body["error"]);
        $this->assertSame(400, $body["error"]["code"]);
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testApiStoreJsonWrong(): void
    {
        $response = $this->client->post("/api/users", [
            "headers" => ["Authorization" => "Bearer {$this->token}", "Content-Type" => "application/json"]
        ]);

        $body = json_decode((string) $response->getBody(), true);

        $this->assertIsArray($body);
        $this->assertArrayHasKey("error", $body);
        $this->assertArrayHasKey("code", $body["error"]);
        $this->assertArrayHasKey("message", $body["error"]);
        $this->assertSame(422, $body["error"]["code"]);
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testApiStore(): void
    {
        $response = $this->client->post("/api/users", [
            "headers" => ["Authorization" => "Bearer {$this->token}", "Content-Type" => "application/json"],
            "json" => [
                "firstname" => "Thiago",
                "lastname" => "Machado",
                "email" => "thiagomachado@email.com",
                "password" => "thiago"
            ]
        ]);

        $body = json_decode((string) $response->getBody(), true);

        $this->assertIsArray($body);
        $this->assertArrayHasKey("id", $body);
        $this->assertArrayHasKey("firstname", $body);
        $this->assertArrayHasKey("lastname", $body);
        $this->assertArrayHasKey("email", $body);
        $this->assertSame(201, $response->getStatusCode());
    }

    public function testApiShowInvalidId(): void
    {
        $response = $this->client->get("/api/users/-1", [
            "headers" => ["Authorization" => "Bearer {$this->token}"]
        ]);

        $body = json_decode((string) $response->getBody(), true);

        $this->assertIsArray($body);
        $this->assertArrayHasKey("error", $body);
        $this->assertArrayHasKey("code", $body["error"]);
        $this->assertArrayHasKey("message", $body["error"]);
        $this->assertSame(400, $body["error"]["code"]);
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testApiShowNotFound(): void
    {
        $response = $this->client->get("/api/users/1914", [
            "headers" => ["Authorization" => "Bearer {$this->token}"]
        ]);

        $body = json_decode((string) $response->getBody(), true);

        $this->assertIsArray($body);
        $this->assertArrayHasKey("error", $body);
        $this->assertArrayHasKey("code", $body["error"]);
        $this->assertArrayHasKey("message", $body["error"]);
        $this->assertSame(404, $body["error"]["code"]);
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testApiShow(): void
    {
        $response = $this->client->get("/api/users/1", [
            "headers" => ["Authorization" => "Bearer {$this->token}"]
        ]);

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey("id", $body);
        $this->assertArrayHasKey("firstname", $body);
        $this->assertArrayHasKey("lastname", $body);
        $this->assertArrayHasKey("email", $body);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testApiUpdateInvalidId(): void
    {
        $response = $this->client->put("/api/users/-1", [
            "headers" => ["Authorization" => "Bearer {$this->token}", "Content-Type" => "application/json"]
        ]);

        $body = json_decode((string) $response->getBody(), true);

        $this->assertIsArray($body);
        $this->assertArrayHasKey("error", $body);
        $this->assertArrayHasKey("code", $body["error"]);
        $this->assertArrayHasKey("message", $body["error"]);
        $this->assertSame(400, $body["error"]["code"]);
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testApiUpdateJsonFailed(): void
    {
        $response = $this->client->put("/api/users/1", [
            "headers" => ["Authorization" => "Bearer {$this->token}", "Content-Type" => "application/json"]
        ]);

        $body = json_decode((string) $response->getBody(), true);

        $this->assertIsArray($body);
        $this->assertArrayHasKey("error", $body);
        $this->assertArrayHasKey("code", $body["error"]);
        $this->assertArrayHasKey("message", $body["error"]);
        $this->assertSame(422, $body["error"]["code"]);
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testApiUpdateJsonEmpty(): void
    {
        $response = $this->client->put("/api/users/1", [
            "headers" => ["Authorization" => "Bearer {$this->token}", "Content-Type" => "application/json"],
            "json" => []
        ]);

        $body = json_decode((string) $response->getBody(), true);

        $this->assertIsArray($body);
        $this->assertArrayHasKey("error", $body);
        $this->assertArrayHasKey("code", $body["error"]);
        $this->assertArrayHasKey("message", $body["error"]);
        $this->assertSame(400, $body["error"]["code"]);
        $this->assertSame(400, $response->getStatusCode());
    }

    public function testApiUpdateJsonWrong(): void
    {
        $response = $this->client->put("/api/users/1", [
            "headers" => ["Authorization" => "Bearer {$this->token}", "Content-Type" => "application/json"]
        ]);

        $body = json_decode((string) $response->getBody(), true);

        $this->assertIsArray($body);
        $this->assertArrayHasKey("error", $body);
        $this->assertArrayHasKey("code", $body["error"]);
        $this->assertArrayHasKey("message", $body["error"]);
        $this->assertSame(422, $body["error"]["code"]);
        $this->assertSame(422, $response->getStatusCode());
    }

    public function testApiUpdateNotFound(): void
    {
        $response = $this->client->put("/api/users/1914", [
            "headers" => ["Authorization" => "Bearer {$this->token}", "Content-Type" => "application/json"],
            "json" => [
                "firstname" => "Thiago",
                "lastname" => "Machado",
                "email" => "thiagomachado@email.com"
            ]
        ]);

        $body = json_decode((string) $response->getBody(), true);

        $this->assertIsArray($body);
        $this->assertArrayHasKey("error", $body);
        $this->assertArrayHasKey("code", $body["error"]);
        $this->assertArrayHasKey("message", $body["error"]);
        $this->assertSame(404, $body["error"]["code"]);
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testApiUpdate(): void
    {
        $response = $this->client->put("/api/users/1", [
            "headers" => ["Authorization" => "Bearer {$this->token}", "Content-Type" => "application/json"],
            "json" => [
                "firstname" => "Thiago",
                "lastname" => "Machado",
                "email" => "thiagomachado@email.com"
            ]
        ]);

        $body = json_decode((string) $response->getBody(), true);

        $this->assertIsArray($body);
        $this->assertArrayHasKey("id", $body);
        $this->assertArrayHasKey("firstname", $body);
        $this->assertArrayHasKey("lastname", $body);
        $this->assertArrayHasKey("email", $body);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testApiDeleteInvalidId(): void
    {
        $response = $this->client->delete("/api/users/-1", [
            "headers" => ["Authorization" => "Bearer {$this->token}"]
        ]);

        $body = json_decode((string) $response->getBody(), true);

        $this->assertIsArray($body);
        $this->assertArrayHasKey("error", $body);
        $this->assertArrayHasKey("code", $body["error"]);
        $this->assertArrayHasKey("message", $body["error"]);
        $this->assertSame(400, $body["error"]["code"]);
        $this->assertSame(400, $response->getStatusCode());
    }
}
