<?php

namespace System\Core;

use PDO;

/**
 * Class BaseModel
 *
 * Base ORM Model with Query Builder, Relationships, and Eager Loading
 *
 * @package System\Core
 */
class BaseModel extends Database
{
    /**
     * Table associated with the model
     *
     * @var string
     */
    protected string $table = '';

    /**
     * Relations to eager load
     *
     * @var array
     */
    protected array $with = [];

    /**
     * Database connection name
     *
     * @var string
     */
    protected string $connection = 'default';

    /**
     * BaseModel constructor.
     *
     * @param string $connection Database connection name
     */
    public function __construct(string $connection = 'default')
    {
        $this->connection = $connection;
        parent::__construct($connection);

        // Auto-load table into query builder if defined
        if ($this->table) {
            parent::table($this->table);
        }
    }

    /**
     * Set table name (Laravel style)
     *
     * @param string $table
     * @return static
     */
    public function table(string $table): static
    {
        $this->table = $table;
        parent::table($table);
        return $this;
    }

    /**
     * Add a WHERE clause
     *
     * @param string $column Column name
     * @param string $operator Comparison operator
     * @param mixed $value Value to compare
     * @return static
     */
    public function where(string $column, string $operator, mixed $value): static
    {
        $this->wheres[] = "{$column} {$operator} ?";
        $this->bindings[] = $value;
        return $this;
    }

    /**
     * Shorthand for WHERE equality
     *
     * @param string $column Column name
     * @param mixed $value Value to match
     * @return static
     */
    public function whereEquals(string $column, mixed $value): static
    {
        return $this->where($column, '=', $value);
    }

    /**
     * Set LIMIT for query
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
     * Eager load relationships
     *
     * @param array $relations
     * @return $this
     */
    public function with(array $relations): static
    {
        $this->with = $relations;
        return $this;
    }

    /**
     * Get all rows with eager loaded relationships
     *
     * @return array
     */
    public function getWith(): array
    {
        $rows = $this->get();

        foreach ($this->with as $name => $callback) {
            foreach ($rows as &$row) {
                $row[$name] = $callback($row);
            }
        }

        $this->with = [];
        return $rows;
    }

    /**
     * Get first row with eager loaded relationships
     *
     * @return array|null
     */
    public function firstWith(): ?array
    {
        $this->limit(1);
        $rows = $this->getWith();
        return $rows[0] ?? null;
    }

    // ---------------------------------
    // RELATIONSHIPS
    // ---------------------------------

    /**
     * hasOne relationship
     *
     * @param string $related Related model class
     * @param string $foreignKey Foreign key in related table
     * @param string $localKey Local key in current table
     * @return callable
     */
    public function hasOne(string $related, string $foreignKey, string $localKey = 'id'): callable
    {
        return function (array $row) use ($related, $foreignKey, $localKey) {
            $model = new $related($this->connection);
            return $model->where($foreignKey, $row[$localKey])->first();
        };
    }

    /**
     * hasMany relationship
     *
     * @param string $related Related model class
     * @param string $foreignKey Foreign key in related table
     * @param string $localKey Local key in current table
     * @return callable
     */
    public function hasMany(string $related, string $foreignKey, string $localKey = 'id'): callable
    {
        return function (array $row) use ($related, $foreignKey, $localKey) {
            $model = new $related($this->connection);
            return $model->where($foreignKey, $row[$localKey])->get();
        };
    }

    /**
     * belongsTo relationship
     *
     * @param string $related Related model class
     * @param string $foreignKey Foreign key in current table
     * @param string $ownerKey Owner key in related table
     * @return callable
     */
    public function belongsTo(string $related, string $foreignKey, string $ownerKey = 'id'): callable
    {
        return function (array $row) use ($related, $foreignKey, $ownerKey) {
            $model = new $related($this->connection);
            return $model->where($ownerKey, $row[$foreignKey])->first();
        };
    }

    /**
     * belongsToMany (pivot table) relationship
     *
     * @param string $related Related model class
     * @param string $pivotTable Pivot table name
     * @param string $foreignKey Foreign key in pivot table
     * @param string $relatedKey Related key in pivot table
     * @return callable
     */
    public function belongsToMany(string $related, string $pivotTable, string $foreignKey, string $relatedKey): callable
    {
        return function (array $row) use ($related, $pivotTable, $foreignKey, $relatedKey) {
            $stmt = $this->pdo->prepare("SELECT * FROM {$pivotTable} WHERE {$foreignKey} = ?");
            $stmt->execute([$row['id']]);
            $pivotRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $model = new $related($this->connection);
            $results = [];

            foreach ($pivotRows as $p) {
                $results[] = $model->where('id', $p[$relatedKey])->first();
            }

            return $results;
        };
    }
}