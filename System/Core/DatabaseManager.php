<?php
namespace System\Core;

use PDO;
use PDOException;

class DatabaseManager
{
    protected static array $connections = [];

    /**
     * Get PDO connection by name (default reads from config)
     */
    public static function connection(string $name = null): PDO
    {
        $default = Config::get('app.default', 'mysql');
        $name ??= $default;

        if (isset(self::$connections[$name])) {
            return self::$connections[$name];
        }

        $config = Config::get("databases.connections.{$name}");

        if (!$config) {
            throw new \Exception("Database configuration '{$name}' not found.");
        }

        $driver = $config['driver'] ?? 'mysql';
        $charset = $config['charset'] ?? 'utf8mb4';
        $dsn = '';

        switch ($driver) {
            case 'mysql':
                $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$charset}";
                break;
            // Future: pgsql, sqlite, sqlsrv
            default:
                throw new \Exception("Database driver '{$driver}' not supported.");
        }

        try {
            $pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT         => $config['persistent'] ?? false,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );

            self::$connections[$name] = $pdo;
            return $pdo;

        } catch (PDOException $e) {
            throw new \Exception("Database connection failed ({$name}): " . $e->getMessage());
        }
    }

    /**
     * Disconnect single connection
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
