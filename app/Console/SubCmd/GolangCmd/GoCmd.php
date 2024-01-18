<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GolangCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\SubCmd\OpenUrlCmd;

class GoCmd extends Command
{
    protected static string $name = 'go';
    protected static string $desc = 'Some useful tool commands for Golang development';

    protected function subCommands(): array
    {
        return [
            GenerateCmd::class,
            PackageUpCmd::class,
            //  List all packages from of the project. from go.mod
            // Search go package from
        ];
    }

    protected function configure(): void
    {
        // $this->flags->addOptByRule($name, $rule);
    }

    /**
     * Do execute command
     *
     * @param Input  $input
     * @param Output $output
     *
     * @return int|mixed
     */
    protected function execute(Input $input, Output $output): mixed
    {
        return $this->showHelp();
    }
}
