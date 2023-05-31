<?php

namespace App\Repository;

use Core\Db\Repository;

class Article extends Repository
{
    protected $table = 'articles';
    protected $primaryKey = 'article_id';

    public function getDataToCombine()
    {
        $sql = "SELECT * FROM @table ORDER BY position ASC, created_at DESC LIMIT 5";
        $query = $this->createQuery($sql);
        return $this->select($query);
    }
}
