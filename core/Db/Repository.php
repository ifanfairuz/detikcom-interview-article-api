<?php

namespace Core\Db;

use Core\Db\Connection\WithConnection;
use Core\Db\Query\Query;
use PDO;

class Repository
{
    use WithConnection;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * insert into database
     * 
     * @param array $params
     * 
     * @return array|false
     */
    public function insert($params)
    {
        try {
            $connection = $this->getConnection();

            $primaryKey = sqlEscapeIdentifier($this->primaryKey);

            $sqlColumns = join(', ', array_map('sqlEscapeIdentifier', array_keys($params)));
            $sqlValues = rtrim(str_repeat("?, ", count($params)), ', ');

            $sql = "INSERT INTO @table ($sqlColumns, created_at, updated_at)";
            $sql .= " VALUES ($sqlValues, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            if ($connection->isPgsql()) {
                $sql .= " RETURNING $primaryKey, created_at";
            }

            $query = $this->createQuery($sql, array_values($params));

            $connection->beginTransaction();

            if ($statement = $connection->execPreparedStatement($query)) {
                $connection->commit();
                if ($connection->isMysql()) {
                    $query = $this->createQuery("SELECT $primaryKey, created_at from @table WHERE $primaryKey = LAST_INSERT_ID()");
                    $result = $connection->execPreparedStatementAndFetch($query);
                } else {
                    $result = $statement->fetch(PDO::FETCH_ASSOC);
                }
                return $result;
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        return false;
    }

    /**
     * update into database
     * 
     * @param array $params
     * @param array $where
     * 
     * @return array|false
     */
    public function updateWithPrimaryKey($params, $id)
    {
        return $this->update($params, [$this->primaryKey => $id]);
    }

    /**
     * update into database
     * 
     * @param array $params
     * @param array $where
     * 
     * @return array|false
     */
    public function update($params, $where)
    {
        $sqlUpdate = "";
        foreach ($params as $column => $value) {
            $sqlUpdate .= sqlEscapeIdentifier($column) . " = ?, ";
        }
        $sqlUpdate .= "updated_at = CURRENT_TIMESTAMP";

        $sqlWhere = $this->genSqlWhere($where);

        $sql = "UPDATE @table SET $sqlUpdate WHERE $sqlWhere RETURNING updated_at";

        $query = $this->createQuery($sql, array_push(array_values($params), ...array_values($where)));

        try {
            $connection = $this->getConnection();
            $connection->beginTransaction();
            if ($statement = $connection->execPreparedStatement($query)) {
                $connection->commit();
                if ($connection->isMysql()) {
                    $query = $this->createQuery("SELECT updated_at from @table WHERE $sqlWhere", [array_values($where)]);
                    $result = $connection->execPreparedStatementAndFetch($query);
                } else {
                    $result = $statement->fetch(PDO::FETCH_ASSOC);
                }
                return $result;
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        return false;
    }

    /**
     * delete from database
     * 
     * @param array $where
     * @return bool
     */
    public function delete($where)
    {
        $sqlWhere = $this->genSqlWhere($where);

        $sql = "DELETE FROM @table WHERE $sqlWhere";

        $query = $this->createQuery($sql, array_values($where));

        try {
            $connection = $this->getConnection();
            $connection->beginTransaction();
            if (!!$connection->execPreparedStatement($query)) {
                $connection->commit();
                return true;
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        return false;
    }

    /**
     * select from database
     * 
     * @param \Core\Db\Query\Query|string $query
     * @return array
     */
    public function select($query)
    {
        $query = is_string($query) ? $this->createQuery($query) : $query->table($this->table);
        return $this->getConnection()->execPreparedStatementAndFetchAll($query);
    }

    /**
     * select from database
     * 
     * @param \Core\Db\Query\Query|string $query
     * @return array|null
     */
    public function selectOne($query)
    {
        $query = is_string($query) ? $this->createQuery($query) : $query->table($this->table);
        return $this->getConnection()->execPreparedStatementAndFetch($query);
    }

    /**
     * create query
     * 
     * @param string $sql
     * @param array $binding
     * 
     * @return \Core\Db\Query\Query
     */
    protected function createQuery($sql, $binding = [])
    {
        return new Query($sql, $binding, $this->table);
    }

    /**
     * create query
     * 
     * @param array $params
     * 
     * @return string
     */
    protected function genSqlWhere($params)
    {
        $sqlWhere = "";
        foreach ($params as $column => $expect) {
            $sqlWhere .= sqlEscapeIdentifier($column) . " = ? AND";
        }
        return rtrim($sqlWhere, " AND");
    }
}
