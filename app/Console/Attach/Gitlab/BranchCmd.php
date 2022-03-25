<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Attach\Gitlab;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Toolkit\PFlag\FlagsParser;

/**
 * Class BranchCreateCmd
 *
 * @package Inhere\Kite\Console\Controller\Gitlab
 */
class BranchCmd extends Command
{
    protected static string $name = 'branch';
    protected static string $desc = 'git branch manage for gitlab project';

    public static function aliases(): array
    {
        return ['br'];
    }

    protected function subCommands(): array
    {
        return [
            BranchInitCmd::class,
            BranchCreateCmd::class,
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
     * @return int|mixed
     */
    protected function execute(Input $input, Output $output): mixed
    {
        return $this->showHelp();
    }
}
