<?php
namespace System\Database\Migration;

use PDO;
use RuntimeException;

/**
 * Schema builder - responsible for creating / dropping tables.
 *
 * Usage:
 * Schema::setConnection($pdo);
 * Schema::create('users', function(Blueprint $table) {
 *     $table->id();
 *     $table->string('email', 150)->unique();
 *     $table->timestamps();
 * });
 *
 * Schema::drop('users');
 */
class Schema
{
    protected static ?PDO $pdo = null;
    protected static string $defaultEngine = 'InnoDB';
    protected static string $defaultCharset = 'utf8mb4';

    public static function setConnection(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    protected static function getConnection(): PDO
    {
        if (!self::$pdo) {
            throw new RuntimeException("Schema: PDO connection not set. Call Schema::setConnection(\$pdo) first.");
        }
        return self::$pdo;
    }

    /**
     * Create table using Blueprint.
     */
    public static function create(string $tableName, callable $callback, array $options = []): void
    {
        $blueprint = new Blueprint($tableName);
        $callback($blueprint);

        $sql = $blueprint->toSqlCreate(self::$defaultEngine, self::$defaultCharset, $options);

        $pdo = self::getConnection();
        $pdo->exec($sql);
    }

    /**
     * Drop table if exists.
     */
    public static function drop(string $tableName): void
    {
        $pdo = self::getConnection();
        $sql = "DROP TABLE IF EXISTS `" . self::escapeIdentifier($tableName) . "`;";
        $pdo->exec($sql);
    }

    /**
     * Escape identifier (very simple).
     */
    public static function escapeIdentifier(string $identifier): string
    {
        // Basic escaping: remove backticks if present then wrap
        $identifier = str_replace('`', '', $identifier);
        return $identifier;
    }
}
