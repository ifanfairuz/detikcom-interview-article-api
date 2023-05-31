<?php

namespace Core\Db\Connection;

use PDO;

/**
 * @var \PDO|null
 */
$con = null;

class Connection
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var \PDO
     */
    private $con;

    public function __construct()
    {
        $this->config = config('db');
        $this->connect();
    }

    /**
     * @return bool
     */
    public function isMysql()
    {
        return $this->config['db_connection'] === 'mysql';
    }

    public function isPgsql()
    {
        return $this->config['db_connection'] === 'pgsql';
    }

    /**
     * generate dsn 
     */
    private function getDsn()
    {
        if ($this->isMysql() && !empty($this->config['db_socket'])) {
            $dsn = $this->config['db_connection'] . ":";
            $dsn .= "unix_socket=" . $this->config['db_socket'] . ";";
        } else {
            $dsn = $this->config['db_connection'] . ":";
            $dsn .= "host=" . $this->config['db_host'] . ";";
        }
        $dsn .= "dbname=" . $this->config['db_name'] . ";";
        $dsn .= "port=" . $this->config['db_port'];

        return $dsn;
    }

    /**
     * connect to database
     */
    public function connect()
    {
        try {
            global $con;

            if (!$con) {
                $con = new \PDO($this->getDsn(), $this->config['db_user'], $this->config['db_password']);
            }

            $this->con = &$con;
        } catch (\PDOException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * close connection
     */
    public function close()
    {
        global $con;

        if ($this->con) $this->con = null;
        if ($con) $con = null;
    }

    /**
     * parse query
     * 
     * @param \Core\Db\Query\Query|string $query
     * 
     * @return string
     */
    private function parseQuery($query)
    {
        return is_string($query) ? $query : $query->toRawSql();
    }

    /**
     * parse query
     * 
     * @param \Core\Db\Query\Query|string $query
     * 
     * @return string
     */
    private function parseParams($query)
    {
        return is_string($query) ? null : $query->getBinding();
    }

    /**
     * exec prepared statement
     * 
     * @param string $name
     * 
     * @return string|false
     */
    public function lastInsertId($name_seq = null)
    {
        return $this->con->lastInsertId($name_seq);
    }

    /**
     * exec prepared statement
     * 
     * @param \Core\Db\Query\Query|string $query
     * 
     * @return \PDOStatement|false
     * 
     * @throws \PDOException
     */
    public function prepareStatement($query)
    {
        $sql = $this->parseQuery($query);
        return $this->con->prepare($sql);
    }

    /**
     * exec statement
     * 
     * @param \Core\Db\Query\Query|string $query
     * 
     * @return int|false
     */
    public function exec($query)
    {
        $sql = $this->parseQuery($query);
        return $this->con->exec($sql);
    }

    /**
     * exec prepared statement
     * 
     * @param \Core\Db\Query\Query|string $query
     * @param array|null $params An array of values with as many elements as there are bound parameters in the SQL statement being executed. All values are treated as PDO::PARAM_STR
     * 
     * @return \PDOStatement|false
     * 
     * @throws \PDOException
     */
    public function execPreparedStatement($query, $params = null)
    {
        $statement = $this->prepareStatement($query);
        $result = $statement->execute($params ?? $this->parseParams($query));
        if ($result) {
            return $statement;
        }

        throw new \Exception("DatabaseError|" . json_encode($statement->errorInfo()) . '|' . $this->parseQuery($query));
        return $result;
    }

    /**
     * exec prepared statement and fetch
     * 
     * @param \Core\Db\Query\Query|string $query
     * @param array|null $params An array of values with as many elements as there are bound parameters in the SQL statement being executed. All values are treated as PDO::PARAM_STR
     * @param string|null $class object class
     * @param array $args
     * 
     * @return array|object|null
     * 
     * @throws \PDOException
     */
    public function execPreparedStatementAndFetch($query, $params = null, $class = null, $args = [])
    {
        try {
            if ($statement = $this->execPreparedStatement($query, $params)) {
                if ($statement->rowCount() > 0) {
                    if ($class && class_exists($class)) {
                        return $statement->fetchObject($class);
                    }

                    return $statement->fetch(PDO::FETCH_ASSOC);
                }
            }

            return null;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * exec prepared statement and fetch
     * 
     * @param \Core\Db\Query\Query|string $query
     * @param array|null $params An array of values with as many elements as there are bound parameters in the SQL statement being executed. All values are treated as PDO::PARAM_STR
     * @param string|null $class object class
     * @param array $args
     * 
     * @return array
     * 
     * @throws \PDOException
     */
    public function execPreparedStatementAndFetchAll($query, $params = null, $class = null, $args = [])
    {
        try {
            if ($statement = $this->execPreparedStatement($query, $params)) {
                if ($class && class_exists($class)) {
                    return $statement->fetchAll(PDO::FETCH_CLASS, $class, $args);
                }

                return $statement->fetchAll(PDO::FETCH_ASSOC);
            }

            return [];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * run statement
     * 
     * @param \Core\Db\Query\Query|string $query
     * @param array|null $params
     * 
     * @return bool
     * 
     * @throws \PDOException
     */
    public function execExist($query, $params = null)
    {
        if (is_string($query)) {
            $sql = $query;
        } else {
            $sql = $query->toRawSql();
            $params = $params ?? $query->getBinding();
        }
        if ($sql == "") return false;
        $res = $this->execPreparedStatementAndFetch("SELECT EXISTS ($sql) as " . sqlEscapeIdentifier('exists'), $params);
        return $res ? $res['exists'] : false;
    }

    /**
     * transaction
     * 
     * @param \Closure $process
     * 
     * @return void
     */
    public function transaction(\Closure $process)
    {
        $this->beginTransaction();
        $process($this);
    }

    /**
     * commit transaction
     * 
     * @param \Closure $process
     * 
     * @return void
     */
    public function beginTransaction()
    {
        if (!$this->con->inTransaction()) {
            $this->con->beginTransaction();
        }
    }

    /**
     * commit transaction
     * 
     * @param \Closure $process
     * 
     * @return void
     */
    public function commit()
    {
        if ($this->con->inTransaction()) {
            $this->con->commit();
        }
    }

    /**
     * rollback transaction
     * 
     * @param \Closure $process
     * 
     * @return void
     */
    public function rollBack()
    {
        if ($this->con->inTransaction()) {
            $this->con->rollBack();
        }
    }
}
