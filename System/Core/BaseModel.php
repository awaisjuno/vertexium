<?php
namespace System\Core;

/**
 * Class Model
 *
 * Base ORM Model with relationships and eager loading
 */
class Model extends Database
{
    protected string $table = '';
    protected array $with = [];

    /**
     * Set table
     */
    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Define relationships to eager load
     */
    public function with(array $relations): self
    {
        $this->with = $relations;
        return $this;
    }

    /**
     * Get all records with eager loading
     */
    public function getWith(): array
    {
        $results = $this->get();

        if (!empty($this->with)) {
            foreach ($this->with as $relation => $callback) {
                foreach ($results as &$row) {
                    $row[$relation] = $callback($row);
                }
            }
        }

        $this->with = [];
        return $results;
    }

    /**
     * Get first record with eager loading
     */
    public function firstWith()
    {
        $this->limit(1);
        $results = $this->getWith();
        return $results[0] ?? null;
    }

    /**
     * hasOne relationship
     */
    public function hasOne(string $relatedModel, string $foreignKey, string $localKey = 'id')
    {
        return function($row) use ($relatedModel, $foreignKey, $localKey) {
            $model = new $relatedModel($this->getConnectionName());
            return $model->where($foreignKey, $row[$localKey])->first();
        };
    }

    /**
     * hasMany relationship
     */
    public function hasMany(string $relatedModel, string $foreignKey, string $localKey = 'id')
    {
        return function($row) use ($relatedModel, $foreignKey, $localKey) {
            $model = new $relatedModel($this->getConnectionName());
            return $model->where($foreignKey, $row[$localKey])->get();
        };
    }

    /**
     * belongsTo relationship
     */
    public function belongsTo(string $relatedModel, string $foreignKey, string $ownerKey = 'id')
    {
        return function($row) use ($relatedModel, $foreignKey, $ownerKey) {
            $model = new $relatedModel($this->getConnectionName());
            return $model->where($ownerKey, $row[$foreignKey])->first();
        };
    }

    /**
     * belongsToMany relationship
     */
    public function belongsToMany(string $relatedModel, string $pivotTable, string $foreignKey, string $relatedKey)
    {
        return function($row) use ($relatedModel, $pivotTable, $foreignKey, $relatedKey) {
            // Use the same database connection as the current model
            $db = $this->getPDO();

            $relatedIds = (new Database($db))->table($pivotTable)
                                ->where($foreignKey, $row['id'])
                                ->get();

            $relatedObj = new $relatedModel($this->getConnectionName());
            $results = [];

            foreach ($relatedIds as $pivot) {
                $results[] = $relatedObj->where('id', $pivot[$relatedKey])->first();
            }

            return $results;
        };
    }

    /**
     * Get the connection name (default)
     */
    protected function getConnectionName(): string
    {
        return property_exists($this, 'connection') ? $this->connection : 'default';
    }

    /**
     * Get the PDO instance from parent Database
     */
    protected function getPDO(): \PDO
    {
        return $this->pdo;
    }
}
