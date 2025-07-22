<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

class Postgres
{
    private static ?PDO $instance = null;

    public static function getInstance(): ?PDO
    {
        if (self::$instance === null) {
            $databaseName = getenv("POSTGRES_DB");

            try {
                self::$instance = new PDO("pgsql:host=database;dbname={$databaseName}", getenv("POSTGRES_USER"), getenv("POSTGRES_PASSWORD"));
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new PDOException("Connection failed: " . $e->getMessage(), $e->getCode());
            }
        }

        return self::$instance;
    }
}
