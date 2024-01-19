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
use Inhere\Kite\Console\SubCmd\ExtCmd\DocCommand;
use Inhere\Kite\Console\SubCmd\ExtCmd\PkgmCommand;

/**
 * Class XCommand
 */
class XCommand extends Command
{
    protected static string $name = 'x';

    protected static string $desc = 'provide some extensions or experiment commands';

    protected function subCommands(): array
    {
        return [
            PkgmCommand::class,
            DocCommand::class,
        ];
    }

    /**
     * do execute
     * @param  Input $input
     * @param  Output $output
     */
    protected function execute(Input $input, Output $output): void
    {
        $this->showHelp();
    }
}
