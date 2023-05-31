<?php

namespace Core\Command;

abstract class Handler
{
    /**
     * @var string|null
     */
    public $command;

    /**
     * @var array
     */
    public $options = [];

    /**
     * @var array
     */
    public $args = [];

    public function __construct($argv)
    {
        $this->parse($argv);
    }

    /**
     * abstract method for handle
     */
    abstract function handle();

    /**
     * parse argv from cli
     */
    public function parse($argv)
    {
        // no argv
        if (count($argv) == 0) {
            return $this->help();
        }

        $this->command = $argv[0];

        // iterate options and args
        for ($i = 1; $i < count($argv); $i++) {
            $arg = $argv[$i];
            $next_arg = @$argv[$i + 1] ?? null;

            if (substr($arg, 0, 2) === '--') {
                // push args
                $this->args[$arg] = null;
                if ($next_arg && substr($next_arg, 0, 1) !== '-') {
                    $this->args[$arg] = $next_arg;
                    $i++;
                }
            } elseif (substr($arg, 0, 1) === '-') {
                // push options
                $this->args[$arg] = null;
                if ($next_arg && substr($next_arg, 0, 1) !== '-') {
                    $this->options[$arg] = $next_arg;
                    $i++;
                }
            } else {
                return $this->invalid();
            }
        }
    }

    /**
     * invalid command callback
     */
    public function invalid()
    {
        $this->help("Invalid commands, see help with --help.");
    }

    /**
     * show help
     * @param string|null $error
     */
    public function help($error = null)
    {
        if ($error) cli_error($error);

        cli_line('');
        cli_line("Available Commands :");

        cli_line(" migrate\t\t: Run database migration.");
        cli_line("   --rollback [step]\t: Arguments to rollback migration, step is the number of migrations to be reverted.");

        cli_line(" seed\t\t: Run database seed.");

        cli_line("\n article\t\t: Get article from api and combine with data from database.\n");
        cli_line("--help to see this help.\n");

        die();
    }
}
