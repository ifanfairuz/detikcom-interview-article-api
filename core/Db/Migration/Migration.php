<?php

namespace Core\Db\Migration;

use Core\Db\Repository;

class Migration extends Repository
{
    protected $table;

    public function __construct()
    {
        $this->table = config('db.migration_table');
    }

    /**
     * get last batch
     * 
     * @return int
     */
    public function getNextBatch()
    {
        $result = $this->selectOne("SELECT batch FROM @table ORDER BY batch DESC LIMIT 1");
        return $result ? ((int) $result['batch']) + 1 : 1;
    }

    /**
     * get last batch
     * 
     * @return array
     */
    public function getMigratedClass()
    {
        $result = $this->select("SELECT migration FROM @table");
        return array_map(function ($res) {
            return $res['migration'];
        }, $result);
    }

    /**
     * get latest migration base on batch
     * 
     * @param int $batch
     * @return array
     */
    public function getMigratedClassFromBatch($batch)
    {
        $result = $this->select($this->createQuery("SELECT migration FROM @table WHERE batch >= ?", [$batch]));
        return array_map(function ($res) {
            return $res['migration'];
        }, $result);
    }

    /**
     * delete migration with specific classname
     * 
     * @param string $migration
     * @return bool
     */
    public function deleteMigration($migration)
    {
        return $this->delete([
            'migration' => $migration
        ]);
    }
}
