<?php

namespace Core\Db\Migration;

class CreateMigrationTable implements DatabaseMigration
{
    public function up()
    {
        return Scheme::createTable(config('db.migration_table'))
            ->autoIncrement('id', Scheme::INT, 11)
            ->column('migration', Scheme::VARCHAR, 255)
            ->column('batch', Scheme::INT, 6)
            ->timestamp();
    }

    public function down()
    {
        return Scheme::dropTableIfExist(config('db.migration_table'));
    }
}
