<?php

namespace System\Core;

use PDO;

/**
 * Class Database
 *
 * A lightweight Query Builder and Database wrapper similar to Laravel's Fluent Builder.
 * Supports chaining for SELECT, WHERE, JOIN, GROUP, ORDER, LIMIT, and CRUD operations.
 *
 * @package System\Core
 */
class Database
{
    /**
     * The active PDO instance.
     *
     * @var PDO
     */
    protected PDO $pdo;

    /**
     * Active table for the query.
     *
     * @var string
     */
    protected string $table = '';

    /**
     * Selected columns.
     *
     * @var array
     */
    protected array $select = ['*'];

    /**
     * WHERE clause conditions.
     *
     * @var array
     */
    protected array $wheres = [];

    /**
     * Parameter bindings for prepared statements.
     *
     * @var array
     */
    protected array $bindings = [];

    /**
     * JOIN clauses.
     *
     * @var array
     */
    protected array $joins = [];

    /**
     * ORDER BY clauses.
     *
     * @var array
     */
    protected array $order = [];

    /**
     * GROUP BY columns.
     *
     * @var array
     */
    protected array $group = [];

    /**
     * LIMIT value.
     *
     * @var int|null
     */
    protected ?int $limit = null;

    /**
     * OFFSET value.
     *
     * @var int|null
     */
    protected ?int $offset = null;

    /**
     * Database constructor.
     *
     * @param string $connection Connection name defined in DatabaseManager.
     */
    public function __construct(string $connection = 'default')
    {
        $this->pdo = DatabaseManager::connection($connection);
    }

    /* ------------------------------------------------------
     * BASE BUILDER METHODS
     * ------------------------------------------------------ */

    /**
     * Set the table for the query.
     *
     * @param string $table
     * @return static
     */
    public function table(string $table): static
    {
        $this->reset();
        $this->table = $table;
        return $this;
    }

    /**
     * Select specific columns.
     *
     * @param mixed ...$columns
     * @return static
     */
    public function select(...$columns): static
    {
        $this->select = $columns;
        return $this;
    }

    /**
     * Add a WHERE clause.
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return static
     */
    public function where(string $column, string $operator, mixed $value): static
    {
        $this->wheres[] = "{$column} {$operator} ?";
        $this->bindings[] = $value;
        return $this;
    }

    /**
     * Add an OR WHERE clause.
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return static
     */
    public function orWhere(string $column, string $operator, mixed $value): static
    {
        $this->wheres[] = "OR {$column} {$operator} ?";
        $this->bindings[] = $value;
        return $this;
    }

    /**
     * Add WHERE IN clause.
     *
     * @param string $column
     * @param array $values
     * @return static
     */
    public function whereIn(string $column, array $values): static
    {
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $this->wheres[] = "{$column} IN ({$placeholders})";
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    /**
     * Add an INNER JOIN clause.
     *
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @return static
     */
    public function join(string $table, string $first, string $operator, string $second): static
    {
        $this->joins[] = "INNER JOIN {$table} ON {$first} {$operator} {$second}";
        return $this;
    }

    /**
     * Add a LEFT JOIN clause.
     *
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @return static
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): static
    {
        $this->joins[] = "LEFT JOIN {$table} ON {$first} {$operator} {$second}";
        return $this;
    }

    /**
     * Add ORDER BY clause.
     *
     * @param string $column
     * @param string $direction
     * @return static
     */
    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->order[] = "{$column} {$direction}";
        return $this;
    }

    /**
     * Add GROUP BY clause.
     *
     * @param string $column
     * @return static
     */
    public function groupBy(string $column): static
    {
        $this->group[] = $column;
        return $this;
    }

    /**
     * Limit results.
     *
     * @param int $limit
     * @return static
     */
    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Offset results.
     *
     * @param int $offset
     * @return static
     */
    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    /* ------------------------------------------------------
     * EXECUTION METHODS
     * ------------------------------------------------------ */

    /**
     * Fetch all results as an associative array.
     *
     * @return array
     */
    public function get(): array
    {
        $sql = $this->buildSelectQuery();
        $stmt = $this->execute($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch a single row.
     *
     * @return array|null
     */
    public function first(): ?array
    {
        $this->limit(1);
        $sql = $this->buildSelectQuery();
        $stmt = $this->execute($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Insert a new record.
     *
     * @param array $data
     * @return bool
     */
    public function insert(array $data): bool
    {
        $columns = implode(",", array_keys($data));
        $placeholders = implode(",", array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        $this->bindings = array_values($data);

        $stmt = $this->execute($sql);
        return $stmt->rowCount() > 0;
    }

    /**
     * Update a record.
     *
     * @param array $data
     * @return bool
     */
    public function update(array $data): bool
    {
        $set = implode(",", array_map(fn($c) => "$c = ?", array_keys($data)));

        $sql = "UPDATE {$this->table} SET {$set}";

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        $this->bindings = array_values($data) + $this->bindings;

        $stmt = $this->execute($sql);
        return $stmt->rowCount() > 0;
    }

    /**
     * Delete records.
     *
     * @return bool
     */
    public function delete(): bool
    {
        $sql = "DELETE FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        $stmt = $this->execute($sql);
        return $stmt->rowCount() > 0;
    }

    /* ------------------------------------------------------
     * RAW QUERY SUPPORT
     * ------------------------------------------------------ */

    /**
     * Execute a raw SQL query.
     *
     * @param string $sql
     * @param array $bindings
     * @return array
     */
    public function raw(string $sql, array $bindings = []): array
    {
        $stmt = $this->execute($sql, $bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ------------------------------------------------------
     * TRANSACTIONS
     * ------------------------------------------------------ */

    /**
     * Begin a database transaction.
     *
     * @return void
     */
    public function begin(): void
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Commit the transaction.
     *
     * @return void
     */
    public function commit(): void
    {
        $this->pdo->commit();
    }

    /**
     * Rollback the transaction.
     *
     * @return void
     */
    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    /* ------------------------------------------------------
     * INTERNAL UTILITY METHODS
     * ------------------------------------------------------ */

    /**
     * Build the final SELECT query string.
     *
     * @return string
     */
    private function buildSelectQuery(): string
    {
        $sql = "SELECT " . implode(",", $this->select) . " FROM {$this->table}";

        if (!empty($this->joins)) {
            $sql .= " " . implode(" ", $this->joins);
        }

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(" AND ", $this->wheres);
        }

        if (!empty($this->group)) {
            $sql .= " GROUP BY " . implode(",", $this->group);
        }

        if (!empty($this->order)) {
            $sql .= " ORDER BY " . implode(",", $this->order);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    /**
     * Prepare and execute a PDO query.
     *
     * @param string $sql
     * @param array $extraBindings
     * @return \PDOStatement
     */
    private function execute(string $sql, array $extraBindings = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge($this->bindings, $extraBindings));

        // Reset builder for next query
        $this->reset();

        return $stmt;
    }

    /**
     * Reset query builder state.
     *
     * @return void
     */
    private function reset(): void
    {
        $this->select = ['*'];
        $this->wheres = [];
        $this->bindings = [];
        $this->joins = [];
        $this->order = [];
        $this->group = [];
        $this->limit = null;
        $this->offset = null;
    }
}
