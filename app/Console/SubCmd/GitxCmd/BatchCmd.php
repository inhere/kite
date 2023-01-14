<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitxCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Throwable;
use Toolkit\PFlag\FlagsParser;

/**
 * Class BranchCmd
 */
class BatchCmd extends Command
{
    protected static string $name = 'batch';
    protected static string $desc = 'batch run or handle git commands';

    public static function aliases(): array
    {
        return ['bat'];
    }

    protected function subCommands(): array
    {
        return [
            BatchStatusCmd::class,
            BatchRunCmd::class,
        ];
    }

    protected function configFlags(FlagsParser $fs): void
    {
        // $this->flags->addOptByRule($name, $rule);
    }

    /**
     * Do execute command
     *
     * @param Input $input
     * @param Output $output
     *
     * @throws Throwable
     */
    protected function execute(Input $input, Output $output): void
    {
        // default run
        $this->showHelp();
    }
}
