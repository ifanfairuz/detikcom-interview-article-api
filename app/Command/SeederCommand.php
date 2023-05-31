<?php

namespace App\Command;

use App\Seeder\DatabaseSeeder;

class SeederCommand
{
    /**
     * handle command line migration
     * 
     * @param array $args
     * @param array $options
     */
    public function handle($args, $options)
    {
        try {
            (new DatabaseSeeder)->run();
        } catch (\Exception $e) {
            cli_error($e->getMessage());
        }
    }
}
