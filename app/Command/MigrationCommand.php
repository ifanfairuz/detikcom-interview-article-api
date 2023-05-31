<?php

namespace App\Command;

use Core\Db\Migration\Migrator;

class MigrationCommand
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
            $migrator = new Migrator();

            if (array_key_exists('--rollback', $args)) {
                return $migrator->migrate(Migrator::MIGRATE_DOWN, $args['--rollback'] ?? 1);
            }

            return $migrator->migrate();
        } catch (\Exception $e) {
            cli_error($e->getMessage());
        }
    }
}
