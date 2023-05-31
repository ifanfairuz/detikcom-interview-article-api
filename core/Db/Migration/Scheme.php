<?php

namespace Core\Db\Migration;

use Core\Db\Connection\WithConnection;

class Scheme
{
    use WithConnection;

    const VARCHAR = 0;
    const INT = 1;
    const TEXT = 2;
    const TIMESTAMP = 3;
    const TINYINT = 4;

    const PGSQL_TYPES = [
        self::VARCHAR => 'VARCHAR(%d)',
        self::INT => 'INT',
        self::TEXT => 'TEXT',
        self::TIMESTAMP => 'TIMESTAMP',
        self::TINYINT => 'SMALLINT',
    ];

    const MYSQL_TYPES = [
        self::VARCHAR => 'VARCHAR(%d)',
        self::INT => 'INT(%d)',
        self::TEXT => 'TEXT',
        self::TIMESTAMP => 'TIMESTAMP',
        self::TINYINT => 'TINYINT',
    ];

    /**
     * @var string create|update|drop
     */
    protected $action = "create";

    /**
     * @var string
     */
    protected $table = "";

    /**
     * @var string|null
     */
    protected $primaryKey = null;

    /**
     * @var string|null
     */
    protected $autoIncrement = null;

    /**
     * @var array column => type
     */
    protected $columns = [];

    /**
     * create table
     * 
     * @param string $table
     * 
     * @return static
     */
    public static function createTable($table)
    {
        return (new self())->action('create')->table($table);
    }

    /**
     * create table
     * 
     * @param string $table
     * 
     * @return static
     */
    public static function dropTableIfExist($table)
    {
        return (new self())->action('drop')->table($table);
    }

    /**
     * set table name
     * @param string $table
     * 
     * @return static
     */
    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * set action
     * @param string $action
     * 
     * @return static
     */
    public function action($action)
    {
        $action = preg_match('/create|alter|drop/s', $action) ? $action : 'create';
        $this->action = $action;
        return $this;
    }

    /**
     * get type
     * 
     * @param int $type
     * @param string|int|null $arg
     * 
     * @return string
     */
    private function genType($type, $arg = null)
    {
        $dbms = config('db.db_connection');
        $types = $dbms === 'mysql' ? self::MYSQL_TYPES : self::PGSQL_TYPES;
        $type = $types[$type];

        if ($arg !== null) {
            $type = sprintf($type, $arg);
        }

        return $type;
    }

    /**
     * set primary key
     * 
     * @param string $primaryKey
     * 
     * @return static
     */
    public function primaryKey($primaryKey)
    {
        if ($this->primaryKey === null) $this->primaryKey = $primaryKey;
        return $this;
    }

    /**
     * set auto increment
     * 
     * @param string $autoIncrement
     * 
     * @return static
     */
    public function autoIncrementColumn($autoIncrement)
    {
        if ($this->autoIncrement === null) $this->autoIncrement = $autoIncrement;
        return $this;
    }

    /**
     * column
     * 
     * @param string $column
     * @param int $type
     * @param mixed $length
     * 
     * @return static
     */
    public function column($column, $type, $length = null, $nullable = false)
    {
        $syntax = $this->genType($type, $length);
        if (!$nullable) $syntax .= " NOT NULL";
        $this->columns[$column] = $syntax;
        return $this;
    }

    /**
     * set auto increment
     * 
     * @param string $column
     * 
     * @return static
     */
    public function autoIncrement($column)
    {
        return $this->column($column, self::INT, 11)
            ->primaryKey($column)
            ->autoIncrementColumn($column);
    }

    /**
     * set timestamp column
     * 
     * @return static
     */
    public function timestamp()
    {
        return $this->column('created_at', self::TIMESTAMP, null, true)
            ->column('updated_at', self::TIMESTAMP, null, true);
    }

    /**
     * create sqls
     * 
     * @return array
     */
    private function createSqls()
    {
        $isMysql = config('db.db_connection') === 'mysql';
        $withAI = $this->autoIncrement !== null;
        $sqls = [];

        if (!$isMysql && $withAI) {
            $seqName = $this->table . '_' . $this->autoIncrement . '_seq';
            $sqls[] = "CREATE SEQUENCE " . sqlEscapeIdentifier($seqName);
        }

        $sqlCreate = "CREATE TABLE " . sqlEscapeIdentifier($this->table);

        $sqlColumns = "";
        foreach ($this->columns as $column => $type) {
            $sqlColumns .= ", " . sqlEscapeIdentifier($column) . " $type";
            if ($withAI && $this->autoIncrement === $column) {
                $sqlColumns .= $isMysql ? " AUTO_INCREMENT" : " DEFAULT nextval('$seqName')";
            }
        }

        if ($this->primaryKey !== null) {
            $sqlColumns .= ", PRIMARY KEY (" . sqlEscapeIdentifier($this->primaryKey) . ")";
        }

        $sqlCreate .= " (" . ltrim($sqlColumns, ', ') . ")";

        $sqls[] = $sqlCreate;

        if (!$isMysql && $withAI) {
            $sqlEnd = "ALTER SEQUENCE " . sqlEscapeIdentifier($seqName);
            $sqlEnd .= " OWNED BY " . sqlEscapeIdentifier($this->table) . "." . sqlEscapeIdentifier($this->autoIncrement);

            $sqls[] = $sqlEnd;
        }

        return $sqls;
    }

    /**
     * drop sqls
     * 
     * @return array
     */
    private function dropSqls()
    {
        return ["DROP TABLE IF EXISTS " . sqlEscapeIdentifier($this->table)];
    }

    /**
     * get sql
     * 
     * @return array
     */
    public function genSqls()
    {
        switch ($this->action) {
            case 'create':
                return $this->createSqls();
            case 'drop':
                return $this->dropSqls();
        }

        return [];
    }
}
