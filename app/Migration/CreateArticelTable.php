<?php

namespace App\Migration;

use Core\Db\Migration\DatabaseMigration;
use Core\Db\Migration\Scheme;

class CreateArticelTable implements DatabaseMigration
{
    public function up()
    {
        return Scheme::createTable('articles')
            ->autoIncrement('article_id', Scheme::INT, 11)
            ->column('title', Scheme::VARCHAR, 100)
            ->column('summary', Scheme::VARCHAR, 500)
            ->column('position', Scheme::TINYINT, 1)
            ->column('author', Scheme::VARCHAR, 100)
            ->timestamp();
    }

    public function down()
    {
        return Scheme::dropTableIfExist('articles');
    }
}
