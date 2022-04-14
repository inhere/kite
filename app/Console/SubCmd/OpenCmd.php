<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * Class OpenCmd
 */
class OpenCmd extends Command
{
    protected static string $name = 'open';
    protected static string $desc = 'open commands';

    protected function subCommands(): array
    {
        return [
            OpenUrlCmd::class,
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
