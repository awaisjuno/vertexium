<?php
namespace System\Core;

use PDO;

class Database
{
    protected PDO $pdo;

    protected string $table = '';
    protected array $select = ['*'];
    protected array $wheres = [];
    protected array $bindings = [];
    protected array $joins = [];
    protected array $order = [];
    protected array $group = [];
    protected ?int $limit = null;
    protected ?int $offset = null;

    public function __construct(string $connection = 'default')
    {
        $this->pdo = DatabaseManager::connection($connection);
    }

    /*----------------------------------------------------
        BASE BUILDER FUNCTIONS
    -----------------------------------------------------*/

    public function table(string $table): static
    {
        $this->reset();
        $this->table = $table;
        return $this;
    }

    public function select(...$columns): static
    {
        $this->select = $columns;
        return $this;
    }

    public function where(string $column, string $operator, mixed $value): static
    {
        $this->wheres[] = "{$column} {$operator} ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function orWhere(string $column, string $operator, mixed $value): static
    {
        $this->wheres[] = "OR {$column} {$operator} ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function whereIn(string $column, array $values): static
    {
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $this->wheres[] = "{$column} IN ({$placeholders})";
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second): static
    {
        $this->joins[] = "INNER JOIN {$table} ON {$first} {$operator} {$second}";
        return $this;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): static
    {
        $this->joins[] = "LEFT JOIN {$table} ON {$first} {$operator} {$second}";
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->order[] = "{$column} {$direction}";
        return $this;
    }

    public function groupBy(string $column): static
    {
        $this->group[] = $column;
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    /*----------------------------------------------------
        EXECUTORS
    -----------------------------------------------------*/

    public function get(): array
    {
        $sql = $this->buildSelectQuery();
        $stmt = $this->execute($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(): ?array
    {
        $this->limit(1);
        $sql = $this->buildSelectQuery();
        $stmt = $this->execute($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function insert(array $data): bool
    {
        $columns = implode(",", array_keys($data));
        $placeholders = implode(",", array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        $this->bindings = array_values($data);

        $stmt = $this->execute($sql);
        return $stmt->rowCount() > 0;
    }

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

    public function delete(): bool
    {
        $sql = "DELETE FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        $stmt = $this->execute($sql);
        return $stmt->rowCount() > 0;
    }

    /*----------------------------------------------------
        RAW QUERIES
    -----------------------------------------------------*/

    public function raw(string $sql, array $bindings = []): array
    {
        $stmt = $this->execute($sql, $bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*----------------------------------------------------
        TRANSACTIONS
    -----------------------------------------------------*/

    public function begin(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    /*----------------------------------------------------
        INTERNAL FUNCTIONS
    -----------------------------------------------------*/

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

    private function execute(string $sql, array $extraBindings = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge($this->bindings, $extraBindings));

        // Reset after execution
        $this->reset();

        return $stmt;
    }

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
