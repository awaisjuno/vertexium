<?php

namespace System\Database\Migration;

/**
 * Class Blueprint
 *
 * Represents a table schema definition used for building SQL CREATE TABLE statements.
 * Allows the addition of various column types, primary keys, and timestamp fields.
 *
 * @package System\Database\Migration
 */
class Blueprint
{
    /**
     * The name of the database table being created.
     *
     * @var string
     */
    protected string $table;

    /**
     * List of column definitions accumulated during the schema build.
     *
     * @var array<int, string>
     */
    protected array $columns = [];

    /**
     * The name of the primary key column for the table.
     *
     * @var string|null
     */
    protected ?string $primaryKey = null;

    /**
     * Blueprint constructor.
     *
     * @param string $table The name of the table being defined.
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /**
     * Add an auto-incrementing UNSIGNED INT primary key column.
     *
     * @param string $name Column name.
     * @return $this
     */
    public function increments(string $name)
    {
        $this->columns[] = "`$name` INT UNSIGNED AUTO_INCREMENT";
        $this->primaryKey = $name;
        return $this;
    }

    /**
     * Add an INT column.
     *
     * @param string $name Column name.
     * @return $this
     */
    public function integer(string $name)
    {
        $this->columns[] = "`$name` INT";
        return $this;
    }

    /**
     * Add a BIGINT column.
     *
     * @param string $name Column name.
     * @return $this
     */
    public function bigInteger(string $name)
    {
        $this->columns[] = "`$name` BIGINT";
        return $this;
    }

    /**
     * Add a VARCHAR column.
     *
     * @param string $name Column name.
     * @param int    $length Maximum length of the string.
     * @return $this
     */
    public function string(string $name, int $length = 255)
    {
        $this->columns[] = "`$name` VARCHAR($length)";
        return $this;
    }

    /**
     * Add a TEXT column.
     *
     * @param string $name Column name.
     * @return $this
     */
    public function text(string $name)
    {
        $this->columns[] = "`$name` TEXT";
        return $this;
    }

    /**
     * Add a BOOLEAN/TINYINT(1) column.
     *
     * @param string $name Column name.
     * @return $this
     */
    public function boolean(string $name)
    {
        $this->columns[] = "`$name` TINYINT(1)";
        return $this;
    }

    /**
     * Add Laravel-style timestamp fields:
     * - created_at
     * - updated_at
     *
     * @return $this
     */
    public function timestamps()
    {
        $this->columns[] = "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $this->columns[] = "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        return $this;
    }

    /**
     * Define the primary key for the table.
     *
     * @param string $name Primary key column name.
     * @return $this
     */
    public function primary(string $name)
    {
        $this->primaryKey = $name;
        return $this;
    }

    /**
     * Generate the final SQL CREATE TABLE statement.
     *
     * @return string SQL query for table creation.
     */
    public function toSql(): string
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (";
        $sql .= implode(", ", $this->columns);

        if ($this->primaryKey !== null) {
            $sql .= ", PRIMARY KEY (`{$this->primaryKey}`)";
        }

        $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        return $sql;
    }
}
