<?php
// src/Database/Database.php

namespace App\Database;

use PDO;
use PDOException;

class Database {
    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct() {
        $server = getenv('DB_SERVER') ?: 'LOOP\SQLEXPRESS';
        $database = getenv('DB_DATABASE') ?: 'MarkbookTracker';
        $username = getenv('DB_USERNAME') ?: 'va';
        $password = getenv('DB_PASSWORD') ?: 'root';

        $dsn = "sqlsrv:Server=$server;Database=$database";
        
        try {
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die(json_encode(["error" => "Database connection failed: " . $e->getMessage()]));
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->connection;
    }
}