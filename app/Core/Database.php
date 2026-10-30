<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

/**
 * PDO Database Manager supporting MySQL 8.0 (Primary) and automatic SQLite fallback for local testing/demo
 */
class Database
{
    private static ?PDO $instance = null;
    private static string $driver = 'mysql';

    public static function getConnection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $connectionType = strtolower((string)env('DB_CONNECTION', 'mysql'));
        $host = (string)env('DB_HOST', '127.0.0.1');
        if ($host === 'db' && !file_exists('/.dockerenv')) {
            // Outside Docker container: skip slow 'db' hostname DNS resolution on Windows
            if ((bool)env('DB_FALLBACK_SQLITE', true)) {
                self::$instance = self::connectSqlite();
                return self::$instance;
            }
            $host = '127.0.0.1';
        }
        $port = (int)env('DB_PORT', 3306);
        $dbName = (string)env('DB_DATABASE', 'gop_gio_volunteer');
        $username = (string)env('DB_USERNAME', 'root');
        $password = (string)env('DB_PASSWORD', '');
        $charset = (string)env('DB_CHARSET', 'utf8mb4');
        $fallbackSqlite = (bool)env('DB_FALLBACK_SQLITE', true);

        if ($connectionType === 'sqlite') {
            self::$instance = self::connectSqlite();
            return self::$instance;
        }

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 3,
            ]);
            self::$driver = 'mysql';
            self::$instance = $pdo;
            return self::$instance;
        } catch (PDOException $e) {
            if ($fallbackSqlite) {
                self::$instance = self::connectSqlite();
                return self::$instance;
            }
            Logger::error('MySQL Connection Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function getDriver(): string
    {
        if (self::$instance === null) {
            self::getConnection();
        }
        return self::$driver;
    }

    /**
     * Set custom PDO connection (used by isolated integration tests)
     */
    public static function setConnection(?PDO $pdo, string $driver = 'sqlite'): void
    {
        self::$instance = $pdo;
        self::$driver = $driver;
    }

    /**
     * Initialize or connect to SQLite database mirror from database/schema.sql & database/seed.sql
     */
    public static function connectSqlite(?string $customPath = null, bool $forceReset = false): PDO
    {
        $sqlitePath = $customPath ?? (STORAGE_PATH . '/gop_gio_local.sqlite');
        $needsInit = $forceReset || !file_exists($sqlitePath) || filesize($sqlitePath) < 4096;

        if ($forceReset && file_exists($sqlitePath)) {
            @unlink($sqlitePath);
        }

        $pdo = new PDO('sqlite:' . $sqlitePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $pdo->exec('PRAGMA foreign_keys = ON;');
        $pdo->exec('PRAGMA busy_timeout = 5000;');

        // Register MySQL-compatible helper functions for SQLite compatibility
        if (method_exists($pdo, 'sqliteCreateFunction')) {
            $pdo->sqliteCreateFunction('SHA2', function ($str, $bits = 256) {
                return hash('sha' . (int)$bits, (string)$str);
            }, 2);
            $pdo->sqliteCreateFunction('NOW', function () {
                return date('Y-m-d H:i:s');
            }, 0);
        }

        self::$driver = 'sqlite';

        if ($needsInit) {
            self::initializeSqliteFromSqlFiles($pdo);
        }

        return $pdo;
    }

    /**
     * Converts MySQL 8.0 DDL & Seed SQL into SQLite-compatible statements and executes them
     */
    public static function initializeSqliteFromSqlFiles(PDO $pdo): void
    {
        $schemaSql = file_get_contents(DATABASE_PATH . '/schema.sql');
        $seedSql = file_get_contents(DATABASE_PATH . '/seed.sql');

        $pdo->exec('PRAGMA foreign_keys = OFF;');

        if ($schemaSql !== false) {
            $statements = self::convertMysqlToSqliteStatements($schemaSql);
            foreach ($statements as $stmt) {
                if (trim($stmt) !== '') {
                    $pdo->exec($stmt);
                }
            }
        }

        if ($seedSql !== false) {
            $seedStatements = self::convertMysqlToSqliteStatements($seedSql);
            foreach ($seedStatements as $stmt) {
                if (trim($stmt) !== '') {
                    $pdo->exec($stmt);
                }
            }
        }

        $pdo->exec('PRAGMA foreign_keys = ON;');
    }

    private static function convertMysqlToSqliteStatements(string $sql): array
    {
        // Strip SQL line comments
        $lines = explode("\n", $sql);
        $cleanLines = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (str_starts_with($trimmed, '--') || str_starts_with($trimmed, 'SET ')) {
                continue;
            }
            $cleanLines[] = $line;
        }
        $cleanSql = implode("\n", $cleanLines);

        // Split by semicolon at end of line
        $rawStatements = preg_split('/;\s*(\r?\n|$)/', $cleanSql) ?: [];
        $result = [];

        foreach ($rawStatements as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '') {
                continue;
            }

            if (stripos($stmt, 'CREATE TABLE') === 0) {
                // Convert MySQL CREATE TABLE syntax for SQLite
                $stmt = preg_replace('/`id`\s+INT\s+UNSIGNED\s+NOT\s+NULL\s+AUTO_INCREMENT/i', '`id` INTEGER PRIMARY KEY AUTOINCREMENT', $stmt);
                $stmt = preg_replace('/INT\s+UNSIGNED/i', 'INTEGER', $stmt);
                $stmt = preg_replace('/TINYINT\s+UNSIGNED/i', 'INTEGER', $stmt);
                $stmt = preg_replace('/TINYINT\(\d+\)/i', 'INTEGER', $stmt);
                $stmt = preg_replace('/ENUM\([^)]+\)/i', ' TEXT', $stmt);
                $stmt = preg_replace('/ON\s+UPDATE\s+CURRENT_TIMESTAMP/i', '', $stmt);
                $stmt = preg_replace('/ENGINE=InnoDB[^;]*/i', '', $stmt);

                // Remove PRIMARY KEY (`id`) since INTEGER PRIMARY KEY AUTOINCREMENT defines it
                $stmt = preg_replace('/,\s*PRIMARY\s+KEY\s*\(`id`\)/i', '', $stmt);
                // Convert UNIQUE KEY `name` (...) to UNIQUE (...)
                $stmt = preg_replace('/UNIQUE\s+KEY\s+`[^`]+`\s*\(([^)]+)\)/i', 'UNIQUE ($1)', $stmt);
                // Remove non-unique KEY `name` (...) inside CREATE TABLE
                $stmt = preg_replace('/,\s*KEY\s+`[^`]+`\s*\([^)]+\)/i', '', $stmt);
                // Clean up any trailing comma before closing parenthesis
                $stmt = preg_replace('/,(\s*\)\s*)$/', '$1', $stmt);
            }

            $result[] = $stmt;
        }

        return $result;
    }
}
