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
use Inhere\Kite\Console\SubCmd\OpenCmd;
use Inhere\Kite\Console\SubCmd\ToolCmd\HashHmacCommand;

/**
 * Class ToolCommand
 */
class ToolCommand extends Command
{
    protected static string $name = 'tool';
    protected static string $desc = 'some little tool commands';

    protected function subCommands(): array
    {
        return [
            OpenCmd::class,
            HashHmacCommand::class,
        ];
    }

    /**
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $this->showHelp();
    }
}
