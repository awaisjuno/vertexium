<?php

class Blueprint
{
    protected string $table;
    protected array $columns = [];
    protected ?string $primaryKey = null;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    // -------------------------------
    // Column Types
    // -------------------------------

    public function increments(string $name)
    {
        $this->columns[] = "$name INT UNSIGNED AUTO_INCREMENT";
        $this->primaryKey = $name;
        return $this;
    }

    public function integer(string $name)
    {
        $this->columns[] = "$name INT";
        return $this;
    }

    public function bigInteger(string $name)
    {
        $this->columns[] = "$name BIGINT";
        return $this;
    }

    public function string(string $name, int $length = 255)
    {
        $this->columns[] = "$name VARCHAR($length)";
        return $this;
    }

    public function text(string $name)
    {
        $this->columns[] = "$name TEXT";
        return $this;
    }

    public function boolean(string $name)
    {
        $this->columns[] = "$name TINYINT(1)";
        return $this;
    }

    // -------------------------------
    // Special Helpers
    // -------------------------------

    public function timestamps()
    {
        $this->columns[] = "created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $this->columns[] = "updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        return $this;
    }

    public function primary(string $name)
    {
        $this->primaryKey = $name;
        return $this;
    }

    // -------------------------------
    // Generate Query
    // -------------------------------

    public function toSql(): string
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->table}` (";

        $sql .= implode(", ", $this->columns);

        if ($this->primaryKey !== null) {
            $sql .= ", PRIMARY KEY (`{$this->primaryKey}`)";
        }

        $sql .= ") ENGINE=InnoDB;";

        return $sql;
    }
}

