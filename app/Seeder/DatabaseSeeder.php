<?php

namespace App\Seeder;

class DatabaseSeeder
{
    public function run()
    {
        (new ArticleSeeder())->run();
    }
}
