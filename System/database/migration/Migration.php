<?php
namespace System\Database\Migration;

use PDO;

/**
 * Base Migration class.
 * Extend this in your migration files.
 *
 * Example:
 * class CreateUsersTable extends Migration
 * {
 *     public function up(): void
 *     {
 *         $this->createTable('users', function(Blueprint $table) {
 *             $table->id('user_id');
 *             $table->string('email', 150)->unique();
 *             $table->string('password', 255);
 *             $table->enum('status', ['0','1'])->default('1');
 *             $table->timestamps();
 *         });
 *     }
 *
 *     public function down(): void
 *     {
 *         $this->dropTable('users');
 *     }
 * }
 */
abstract class Migration
{
    protected PDO $pdo;

    /**
     * Accept a PDO instance (or any object that implements PDO interface).
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        // Pass PDO to Schema so static helpers work inside migration
        Schema::setConnection($this->pdo);
    }

    /**
     * Run the migration.
     */
    abstract public function up(): void;

    /**
     * Reverse the migration.
     */
    abstract public function down(): void;

    /**
     * Convenience wrapper for Schema::create
     */
    protected function createTable(string $name, callable $callback): void
    {
        Schema::create($name, $callback);
    }

    /**
     * Convenience wrapper for Schema::drop
     */
    protected function dropTable(string $name): void
    {
        Schema::drop($name);
    }

    /**
     * Execute raw SQL using the PDO instance.
     * Returns number of affected rows or false on failure.
     */
    protected function execute(string $sql)
    {
        return $this->pdo->exec($sql);
    }
}
