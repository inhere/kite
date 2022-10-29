<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitlabCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * Class ProjectInit
 *
 * @package Inhere\Kite\Console\Controller\Gitlab
 */
class ProjectCmd extends Command
{
    protected static string $name = 'project';
    protected static string $desc = 'project manage commands for a gitlab project';

    public static function aliases(): array
    {
        return ['proj'];
    }

    protected function subCommands(): array
    {
        return [
            ProjectInitCmd::class,
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
