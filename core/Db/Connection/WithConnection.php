<?php

namespace Core\Db\Connection;

trait WithConnection
{
    /**
     * @var \Core\Db\Connection\Connection
     */
    private $connection;

    /**
     * get current or create new connection if not exist
     * 
     * @return \Core\Db\Connection\Connection
     */
    public function getConnection()
    {
        if (!$this->connection) $this->connection = new Connection();
        return $this->connection;
    }
}
