<?php
namespace System\Database\Migration;

use System\Database\DB;
use PDO;

class MigrationRunner
{
    private static PDO $pdo;
    private static string $migrationPath = __DIR__ . "/../../../app/database/migrations";
    private static string $migrationTable = "migrations";

    public static function init(): void
    {
        self::$pdo = DB::pdo();
        self::createMigrationTable();
    }

    private static function createMigrationTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . self::$migrationTable . "` (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;";
        self::$pdo->exec($sql);
    }

    public static function run(): void
    {
        self::init();

        $files = glob(self::$migrationPath . "/*.php");
        $executed = self::getExecutedMigrations();
        $batch = self::getNextBatchNumber();

        foreach ($files as $file) {
            $name = basename($file);

            if (in_array($name, $executed)) {
                echo "Already migrated: {$name}\n";
                continue;
            }

            echo "Migrating: {$name}\n";

            $migrationClass = require $file;

            $migration = is_object($migrationClass) ? $migrationClass : new $migrationClass();
            $migration->up();

            self::storeMigrationRecord($name, $batch);
            echo "Migrated: {$name}\n";
        }
    }

    // -------------------------
    // Helper methods
    // -------------------------

    private static function getExecutedMigrations(): array
    {
        $stmt = self::$pdo->query("SELECT migration FROM `" . self::$migrationTable . "`");
        return $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
    }

    private static function getNextBatchNumber(): int
    {
        $stmt = self::$pdo->query("SELECT MAX(batch) FROM `" . self::$migrationTable . "`");
        $lastBatch = $stmt ? (int)$stmt->fetchColumn() : 0;
        return $lastBatch + 1;
    }

    private static function storeMigrationRecord(string $name, int $batch): void
    {
        $stmt = self::$pdo->prepare("INSERT INTO `" . self::$migrationTable . "` (migration, batch) VALUES (?, ?)");
        $stmt->execute([$name, $batch]);
    }

    public static function rollback(): void
    {
        self::init();

        $stmt = self::$pdo->query("SELECT MAX(batch) FROM `" . self::$migrationTable . "`");
        $lastBatch = $stmt ? (int)$stmt->fetchColumn() : 0;

        if ($lastBatch === 0) {
            echo "Nothing to rollback.\n";
            return;
        }

        $stmt = self::$pdo->prepare("SELECT migration FROM `" . self::$migrationTable . "` WHERE batch = ?");
        $stmt->execute([$lastBatch]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($rows as $migrationName) {
            $file = self::$migrationPath . "/" . $migrationName;
            if (!file_exists($file)) continue;

            $migrationClass = require $file;
            $migration = is_object($migrationClass) ? $migrationClass : new $migrationClass();
            $migration->down();

            self::$pdo->prepare("DELETE FROM `" . self::$migrationTable . "` WHERE migration = ?")
                     ->execute([$migrationName]);

            echo "Rolled back: {$migrationName}\n";
        }
    }
}
