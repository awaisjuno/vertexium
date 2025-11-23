<?php

namespace System\Core;

use PDO;
use PDOException;

class DatabaseManager
{
    protected static array $connections = [];

    /**
     * Get PDO connection by name (default uses config default)
     */
    public static function connection(string $name = null): PDO
    {
        $config = Config::get("app.connections.mysql");

        if (!$config || !is_array($config)) {
            throw new \Exception("Database config '{$name}' not found or invalid");
        }

        $driver  = $config['driver'] ?? 'mysql';
        $charset = $config['charset'] ?? 'utf8mb4';

        // Only MySQL supported for now
        if ($driver !== 'mysql') {
            throw new \Exception("Database driver '{$driver}' not supported");
        }

        $dsn = sprintf(
            "mysql:host=%s;port=%d;dbname=%s;charset=%s",
            $config['host'],
            $config['port'] ?? 3306,
            $config['database'],
            $charset
        );

        try {
            $pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_PERSISTENT         => $config['persistent'] ?? false,
                ]
            );

            self::$connections[$name] = $pdo;
            return $pdo;

        } catch (PDOException $e) {
            throw new \Exception("Database connection failed ({$name}): " . $e->getMessage());
        }
    }

    /**
     * Disconnect a single connection
     */
    public static function disconnect(string $name): void
    {
        unset(self::$connections[$name]);
    }

    /**
     * Disconnect all connections
     */
    public static function disconnectAll(): void
    {
        self::$connections = [];
    }
}
