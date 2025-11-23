<?php

namespace System\Database\Migration;

use PDO;

/**
 * Class Migration
 *
 * Base abstract migration class providing database connection handling,
 * schema helpers, and execution utilities. Every migration must implement
 * the `up()` and `down()` methods to define changes made to the database.
 *
 * @package System\Database\Migration
 */
abstract class Migration
{
    /**
     * The PDO database connection used by migrations.
     *
     * @var PDO
     */
    protected PDO $pdo;

    /**
     * Migration constructor.
     *
     * Initializes the PDO connection and attaches it to the Schema builder.
     */
    public function __construct()
    {
        $this->pdo = self::getPDO();
        Schema::setConnection($this->pdo);
    }

    /**
     * Apply the migration changes (create tables, add columns, etc.).
     *
     * @return void
     */
    abstract public function up(): void;

    /**
     * Reverse the migration changes (drop tables, remove columns, etc.).
     *
     * @return void
     */
    abstract public function down(): void;

    /**
     * Create a database table using a schema callback.
     *
     * @param string   $name     The table name.
     * @param callable $callback A callback receiving a Blueprint instance.
     * @return void
     */
    protected function createTable(string $name, callable $callback): void
    {
        Schema::create($name, $callback);
    }

    /**
     * Drop a database table.
     *
     * @param string $name The name of the table to drop.
     * @return void
     */
    protected function dropTable(string $name): void
    {
        Schema::drop($name);
    }

    /**
     * Execute a raw SQL statement through PDO.
     *
     * @param string $sql The SQL string to execute.
     * @return int|false The number of affected rows, or false on failure.
     */
    protected function execute(string $sql)
    {
        return $this->pdo->exec($sql);
    }

    /**
     * Load database configuration and return a PDO instance.
     *
     * @return PDO
     *
     * @throws \RuntimeException If configuration is missing or invalid.
     */
    private static function getPDO(): PDO
    {
        $config = include __DIR__ . '/../../../config/app.php';

        $db = $config['connections']['mysql'];

        $dsn = sprintf(
            "mysql:host=%s;port=%d;dbname=%s;charset=%s",
            $db['host'],
            $db['port'],
            $db['database'],
            $db['charset']
        );

        return new PDO($dsn, $db['username'], $db['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}
