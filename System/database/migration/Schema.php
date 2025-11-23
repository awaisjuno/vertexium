<?php
namespace System\Database\Migration;

use PDO;
use RuntimeException;

class Schema
{
    protected static ?PDO $pdo = null;

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

    public static function create(string $tableName, callable $callback): void
    {
        $blueprint = new Blueprint($tableName);
        $callback($blueprint);
        $sql = $blueprint->toSql();
        self::getConnection()->exec($sql);
    }

    public static function drop(string $tableName): void
    {
        $sql = "DROP TABLE IF EXISTS `" . $tableName . "`;";
        self::getConnection()->exec($sql);
    }
}
