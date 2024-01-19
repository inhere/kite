<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Command;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\SubCmd\DevCmd\CheatCommand;
use Inhere\Kite\Console\SubCmd\DevCmd\LinuxCommand;
use Inhere\Kite\Console\SubCmd\DevCmd\ProjectCmd;
use Inhere\Kite\Console\SubCmd\GolangCmd\GoCmd;
use Inhere\Kite\Console\SubCmd\JavaCmd\JavaCmd;

/**
 * Class DevCommand
 */
class DevCommand extends Command
{
    protected static string $name = 'dev';

    protected static string $desc = 'provide some commands for development';

    protected function subCommands(): array
    {
        return [
            CheatCommand::class,
            LinuxCommand::class,
            ProjectCmd::class,
            GoCmd::class,
            JavaCmd::class,
        ];
    }

    /**
     * do execute
     * @param  Input $input
     * @param  Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $this->showHelp();
    }
}
