<?php

namespace App\Core;

use mysqli;
use mysqli_result;
use mysqli_sql_exception;

class Database {
    private static ?mysqli $connection = null;

    /**
     * Get the single shared MySQLi database connection instance.
     */
    public static function getConnection(): mysqli {
        if (self::$connection === null) {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            try {
                self::$connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
                self::$connection->set_charset('utf8mb4');
            } catch (mysqli_sql_exception $e) {
                if (APP_ENV === 'development') {
                    die('Database connection failed: ' . $e->getMessage());
                }
                die('Service temporarily unavailable. Please try again later.');
            }
        }
        return self::$connection;
    }

    /**
     * Run a parameterized query and return the result set.
     */
    public static function query(string $sql, string $types = '', ...$params): mysqli_result|bool {
        $stmt = self::getConnection()->prepare($sql);
        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    /**
     * Execute a statement (insert, update, delete) and return status.
     */
    public static function execute(string $sql, string $types = '', ...$params): bool {
        $stmt = self::getConnection()->prepare($sql);
        if ($types && $params) {
            $stmt->bind_param($types, ...$params);
        }
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Fetch all matching rows as an associative array.
     */
    public static function fetchAll(string $sql, string $types = '', ...$params): array {
        $result = self::query($sql, $types, ...$params);
        return ($result instanceof mysqli_result) ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Fetch a single row as an associative array.
     */
    public static function fetchOne(string $sql, string $types = '', ...$params): array {
        $result = self::query($sql, $types, ...$params);
        return ($result instanceof mysqli_result) ? ($result->fetch_assoc() ?? []) : [];
    }

    /**
     * Count helper.
     */
    public static function count(string $sql, string $types = '', ...$params): int {
        $row = self::fetchOne($sql, $types, ...$params);
        return (int) ($row['c'] ?? $row[array_key_first($row)] ?? 0);
    }

    /**
     * Get the last inserted ID.
     */
    public static function getLastInsertId(): int {
        return (int)self::getConnection()->insert_id;
    }
}
