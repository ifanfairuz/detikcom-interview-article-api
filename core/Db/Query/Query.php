<?php

namespace Core\Db\Query;

class Query
{

    protected $sql;
    public $table;
    protected $binding;

    public function __construct($sql = "", $binding = [], $table = "")
    {
        $this->sql = $sql;
        $this->binding = $binding;
        $this->table = $table;
    }

    /**
     * set tablename
     * 
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
     * set sql
     * 
     * @param string $sql
     * 
     * @return static
     */
    public function sql($sql)
    {
        $this->sql = $sql;
        return $this;
    }

    /**
     * bind value to column
     * 
     * @param array $binding
     * 
     * @return static
     */
    public function setBind($binding)
    {
        $this->binding = $binding;
        return $this;
    }

    /**
     * bind value to column
     * 
     * @param mixed $value
     * 
     * @return static
     */
    public function bind($value)
    {
        $this->binding[] = $value;
        return $this;
    }

    /**
     * get binding values
     * 
     * @return array|null
     */
    public function getBinding()
    {
        return $this->binding && count($this->binding) > 0 ? array_values($this->binding) : null;
    }

    /**
     * to sql
     * 
     * @return string
     */
    public function toRawSql()
    {
        return str_replace('@table', $this->table, $this->sql);
    }
}
