<?php

namespace System\Database\Migration;

/**
 * Class Blueprint
 *
 * Represents a table schema and allows defining columns using a fluent API.
 * Blueprint is responsible for building column definitions for CREATE TABLE.
 *
 * @package System\Database\Migration
 */
class Blueprint
{
    /**
     * Name of the table being created.
     *
     * @var string
     */
    protected string $table;

    /**
     * List of generated column definitions.
     *
     * @var array<int, string>
     */
    protected array $columns = [];

    /**
     * Holds the last added column name to support chainable modifiers.
     *
     * @var string|null
     */
    protected ?string $lastColumn = null;

    /**
     * Table primary key.
     *
     * @var string|null
     */
    protected ?string $primaryKey = null;

    /**
     * Blueprint constructor.
     *
     * @param string $table Table name being created.
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * Internal helper: store the last created column name.
     *
     * @param string $name
     * @return void
     */
    protected function setLastColumn(string $name): void
    {
        $this->lastColumn = $name;
    }

    /**
     * Add an auto-incrementing primary key (INT UNSIGNED).
     *
     * @param string $name
     * @return $this
     */
    public function increments(string $name): self
    {
        $this->columns[] = "`$name` INT UNSIGNED AUTO_INCREMENT";
        $this->primaryKey = $name;
        $this->setLastColumn($name);

        return $this;
    }

    /**
     * Add a standard INT column.
     *
     * @param string $name
     * @return $this
     */
    public function integer(string $name): self
    {
        $this->columns[] = "`$name` INT";
        $this->setLastColumn($name);

        return $this;
    }

    /**
     * Add a BIGINT column.
     *
     * @param string $name
     * @return $this
     */
    public function bigInteger(string $name): self
    {
        $this->columns[] = "`$name` BIGINT";
        $this->setLastColumn($name);

        return $this;
    }

    /**
     * Add a TINYINT(1) column (usually used for boolean).
     *
     * @param string $name
     * @return $this
     */
    public function tinyInteger(string $name): self
    {
        $this->columns[] = "`$name` TINYINT(1)";
        $this->setLastColumn($name);

        return $this;
    }

    /**
     * Add a VARCHAR column.
     *
     * @param string $name
     * @param int $length
     * @return $this
     */
    public function string(string $name, int $length = 255): self
    {
        $this->columns[] = "`$name` VARCHAR($length)";
        $this->setLastColumn($name);

        return $this;
    }

    /**
     * Add a TEXT column.
     *
     * @param string $name
     * @return $this
     */
    public function text(string $name): self
    {
        $this->columns[] = "`$name` TEXT";
        $this->setLastColumn($name);

        return $this;
    }

    /**
     * Add a boolean column.
     *
     * @param string $name
     * @return $this
     */
    public function boolean(string $name): self
    {
        $this->columns[] = "`$name` TINYINT(1)";
        $this->setLastColumn($name);

        return $this;
    }

    /**
     * Add a DATE column.
     *
     * @param string $name
     * @return $this
     */
    public function date(string $name): self
    {
        $this->columns[] = "`$name` DATE";
        $this->setLastColumn($name);

        return $this;
    }

    /**
     * Add a TIME column.
     *
     * @param string $name
     * @return $this
     */
    public function time(string $name): self
    {
        $this->columns[] = "`$name` TIME";
        $this->setLastColumn($name);

        return $this;
    }

    /**
     * Add created_at and updated_at timestamps.
     *
     * @return $this
     */
    public function timestamps(): self
    {
        $this->columns[] = "`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $this->columns[] = "`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";

        return $this;
    }

    /**
     * Apply DEFAULT value to the last added column.
     *
     * @param mixed $value
     * @return $this
     */
    public function default(mixed $value): self
    {
        if ($this->lastColumn === null) {
            return $this;
        }

        $formatted = is_string($value) ? "'$value'" : $value;

        $this->appendToLastColumn("DEFAULT $formatted");

        return $this;
    }

    /**
     * Mark the last added column as nullable.
     *
     * @return $this
     */
    public function nullable(): self
    {
        if ($this->lastColumn !== null) {
            $this->appendToLastColumn("NULL");
        }

        return $this;
    }

    /**
     * Append additional SQL to the last column definition.
     *
     * @param string $sql
     * @return void
     */
    protected function appendToLastColumn(string $sql): void
    {
        foreach ($this->columns as $index => $column) {
            if (str_contains($column, "`{$this->lastColumn}`")) {
                $this->columns[$index] .= " $sql";
                break;
            }
        }
    }

    /**
     * Add a UNIQUE constraint.
     *
     * @param string|null $column
     * @return $this
     */
    public function unique(string $column = null): self
    {
        $column = $column ?? $this->lastColumn;

        if ($column !== null) {
            $this->columns[] = "UNIQUE (`$column`)";
        }

        return $this;
    }

    /**
     * Set a column as the primary key.
     *
     * @param string $name
     * @return $this
     */
    public function primary(string $name): self
    {
        $this->primaryKey = $name;

        return $this;
    }

    /**
     * Generate the full CREATE TABLE SQL query.
     *
     * @return string
     */
    public function toSql(): string
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (";
        $sql .= implode(', ', $this->columns);

        if ($this->primaryKey !== null) {
            $sql .= ", PRIMARY KEY (`{$this->primaryKey}`)";
        }

        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        return $sql;
    }
}
