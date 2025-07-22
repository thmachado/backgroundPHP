<?php

declare(strict_types=1);

use App\Postgres;
use PHPUnit\Framework\TestCase;

final class PostgresTest extends TestCase
{
    private PDO $database;

    protected function setUp(): void
    {
        $this->database = Postgres::getInstance();
    }

    public function testConnectionIsInstanceOfPDO(): void
    {
        $this->assertInstanceOf(PDO::class, $this->database);
    }

    public function testReturnsTheSameInstance(): void
    {
        $this->assertSame(Postgres::getInstance(), $this->database);
    }

    public function testConnectionIsNotNull(): void
    {
        $this->assertNotNull($this->database);
    }

    public function testConnectionAttributes(): void
    {
        $this->assertSame(PDO::ERRMODE_EXCEPTION, $this->database->getAttribute(PDO::ATTR_ERRMODE));
        $this->assertSame(PDO::FETCH_ASSOC, $this->database->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE));
    }
}
