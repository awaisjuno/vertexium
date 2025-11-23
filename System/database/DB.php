<?php

namespace System\Database;

use System\Core\DatabaseManager;
use PDO;

class DB
{
    public static function pdo(): PDO
    {
        return DatabaseManager::connection('default');
    }

    public static function statement(string $sql): bool
    {
        return self::pdo()->exec($sql) !== false;
    }

    public static function select(string $sql): array
    {
        return self::pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function tableExists(string $table): bool
    {
        $result = self::select("SHOW TABLES LIKE '{$table}'");
        return count($result) > 0;
    }
}
