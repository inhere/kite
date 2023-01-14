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
class BranchCmd extends Command
{
    protected static string $name = 'branch';
    protected static string $desc = 'git branch manage tool command';

    public static function aliases(): array
    {
        return ['br'];
    }

    protected function subCommands(): array
    {
        return [
            BranchListCmd::class,
            BranchCleanCmd::class,
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
        // $bcCmd = new BranchListCmd($input, $output);
        // $bcCmd->run($this->flags->getFlags());
    }
}
