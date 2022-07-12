<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\SubCmd\GitxCmd\GitTagListCmd;
use Throwable;

/**
 * class GitTagCmd
 *
 * @author inhere
 * @date 2022/7/12
 */
class GitTagCmd extends Command
{
    protected static string $name = 'tag';
    protected static string $desc = 'git tag manage tool command. eg: list, add, del';

    protected function subCommands(): array
    {
        return [
            GitTagListCmd::class,
        ];
    }

    /**
     * @param Input $input
     * @param Output $output
     *
     * @throws Throwable
     */
    protected function execute(Input $input, Output $output)
    {
        // default run
        $bcCmd = new GitTagListCmd($input, $output);
        $bcCmd->run($this->flags->getFlags());
    }
}
