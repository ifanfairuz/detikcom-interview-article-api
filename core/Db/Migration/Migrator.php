<?php

namespace Core\Db\Migration;

use Core\Db\Connection\WithConnection;
use Core\Db\Exception\NotSupportDbmsException;
use Core\Db\Query\Query;

class Migrator
{
    use WithConnection;

    const MIGRATE_UP = 0;
    const MIGRATE_DOWN = 1;

    /**
     * @var \Core\Db\Migration\Migration
     */
    private $migration;

    /**
     * @var int
     */
    private $batch = 1;

    public function __construct()
    {
        $this->migration = new Migration();
    }

    /**
     * migrate up
     * 
     * @param \Core\Db\Migration\DatabaseMigration $migration
     * @return void
     */
    public function up(DatabaseMigration $migration)
    {
        $sqls = $migration->up()->genSqls();
        $classname = "\\" . get_class($migration);
        $callback = null;
        if ($classname !== CreateMigrationTable::class) {
            $callback = function () use ($classname) {
                $this->migration->insert(['migration' => $classname, 'batch' => $this->batch]);
                if (php_sapi_name() === 'cli') {
                    cli_success($classname . "\t\tmigrated.");
                }
            };
        }

        $this->run($sqls, $callback);
    }

    /**
     * migrate down
     * 
     * @param \Core\Db\Migration\DatabaseMigration $migration
     * @return void
     */
    public function down(DatabaseMigration $migration)
    {
        $sqls = $migration->down()->genSqls();
        $classname = "\\" . get_class($migration);
        $callback = function () use ($classname) {
            $this->migration->deleteMigration($classname);
            if (php_sapi_name() === 'cli') {
                cli_error($classname . "\t\trollbacked.");
            }
        };

        $this->run($sqls, $callback);
    }

    /**
     * migrate up
     * 
     * @param int $flag
     * @return void
     */
    public function migrate($flag = self::MIGRATE_UP, $step = 1)
    {
        $this->initMigrationTable();

        switch ($flag) {
            case self::MIGRATE_UP:
                $this->migrateUp();
                break;
            case self::MIGRATE_DOWN:
                $this->migrateDown($step);
                break;
        }
    }

    /**
     * migrate up
     * 
     * @return void
     */
    private function migrateUp()
    {
        $done = 0;
        $migrated = $this->migration->getMigratedClass();
        $migrations = glob(app_dir('Migration/*.php'));
        foreach ($migrations as $migration) {
            $classname = "\\App\\Migration\\" . pathinfo($migration, PATHINFO_FILENAME);
            if (!in_array($classname, $migrated) && class_exists($classname)) {
                $this->up(new $classname);
                $done++;
            }
        }

        if (php_sapi_name() === 'cli' && $done == 0) {
            cli_line("Notihing to migrate.");
        }
    }

    /**
     * migrate up
     * 
     * @param int $step
     * @return void
     */
    private function migrateDown($step = 1)
    {
        $migrated = $this->migration->getMigratedClassFromBatch($this->batch - $step);
        foreach ($migrated as $classname) {
            if (class_exists(ltrim($classname, '\\'))) $this->down(new $classname);
        }

        if (php_sapi_name() === 'cli' && count($migrated) == 0) {
            cli_line("Notihing to rollback.");
        }
    }

    /**
     * run migration
     * 
     * @param string[] $sqls
     * @param \Closure|null $callback
     * @return void
     */
    private function run($sqls, \Closure $callback)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            foreach ($sqls as $sql) {
                $res = $connection->execPreparedStatement($sql);
                if ($res === false) {
                    $connection->rollBack();
                    break;
                }
            }
            if ($callback) $callback();
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            if (php_sapi_name() === 'cli') {
                cli_error("Error: " . $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * check table exist
     * 
     * @param string $tablename
     * @return bool
     */
    private function tableExist($tablename)
    {
        $config = config('db');
        $dbname = $config['db_name'];
        switch ($config['db_connection']) {
            case 'pgsql':
                $sql = "SELECT tablename FROM pg_tables WHERE schemaname = 'public' AND tablename  = ?";
                break;
            case 'mysql':
                $sql = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA LIKE '$dbname' AND TABLE_TYPE LIKE 'BASE TABLE' AND TABLE_NAME = ?";
                break;
            default:
                throw new NotSupportDbmsException();
                break;
        }
        $connection = $this->getConnection();
        return $connection->execExist(new Query($sql, [$tablename]));
    }

    /**
     * init migration table
     * 
     * @return void
     */
    private function initMigrationTable()
    {
        $exist = $this->tableExist(config('db.migration_table'));
        if (!$exist) $this->up(new CreateMigrationTable());

        $this->batch = $this->migration->getNextBatch();
    }
}
