<?php
// ARQUIVO: app/core/database.php

class Database
{
    protected ?PDO $db = null;

    public function getDb(): ?PDO
    {
        return $this->db;
    }

    public function __construct()
    {
        if ($this->db === null) {
            $this->conection(
                (string) ($_ENV['DB_HOST'] ?? '127.0.0.1'),
                (string) ($_ENV['DB_NAME'] ?? ''),
                (string) ($_ENV['DB_USER'] ?? 'root'),
                (string) ($_ENV['DB_PASSWORD'] ?? ''),
            );
        }
    }

    private function conection(string $host, string $dbName, string $dbUser, string $dbPassword): void
    {
        try {
            $this->db = new PDO(
                "mysql:host={$host};dbname={$dbName};charset=utf8mb4",
                $dbUser,
                $dbPassword,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
}
