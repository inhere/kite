<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitxCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
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

    public static function aliases(): array
    {
        return ['tg'];
    }

    protected function subCommands(): array
    {
        return [
            GitTagListCmd::class,
            GitTagDelCmd::class,
            GitTagCreateCmd::class,
        ];
    }

    protected function getOptions(): array
    {
        return [
            '--try,--dry-run' => 'bool;Dry-run the workflow, dont real execute',
            '-y, --yes'       => 'bool;Direct execution without confirmation',
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
