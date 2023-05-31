<?php

namespace App\Command;

use Core\Command\Handler;

class CommandHandler extends Handler
{
    /**
     * handle command line
     * 
     */
    public function handle()
    {
        switch ($this->command) {
            case 'migrate':
                (new MigrationCommand())->handle($this->args, $this->options);
                break;

            case 'seed':
                (new SeederCommand())->handle($this->args, $this->options);
                break;

            case 'article':
                (new ArticleCommand())->handle($this->args, $this->options);
                break;

            default:
                $this->help();
                break;
        }
    }
}
