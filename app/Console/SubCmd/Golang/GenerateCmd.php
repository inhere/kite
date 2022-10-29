<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\Golang;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * class GenerateCmd
 *
 * @author inhere
 */
class GenerateCmd extends Command
{
    protected static string $name = 'generate';
    protected static string $desc = 'generate golang source codes';

    public static function aliases(): array
    {
        return ['gen'];
    }

    protected function subCommands(): array
    {
        return [
            GenerateStructCmd::class,
        ];
    }

    /**
     * Do execute command
     *
     * @param Input $input
     * @param Output $output
     *
     * @return mixed|void
     */
    protected function execute(Input $input, Output $output)
    {
        return $this->showHelp();
    }
}
