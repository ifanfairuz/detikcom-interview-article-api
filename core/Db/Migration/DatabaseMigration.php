<?php

namespace Core\Db\Migration;

interface DatabaseMigration
{
    /**
     * migration up
     * 
     * @return \Core\Db\Migration\Scheme
     */
    public function up();

    /**
     * migration down
     * 
     * @return \Core\Db\Migration\Scheme
     */
    public function down();
}
