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
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

                self::$instance->exec("CREATE TABLE IF NOT EXISTS users (
                    id SERIAL PRIMARY KEY,
                    firstname VARCHAR(255) NOT NULL,
                    lastname VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    password TEXT NOT NULL,
                    created_at DATE DEFAULT CURRENT_DATE)
                ");
            } catch (PDOException $e) {
                throw new PDOException("Connection failed: " . $e->getMessage(), $e->getCode());
            }
        }

        return self::$instance;
    }
}
