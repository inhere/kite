<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\Gitflow;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Throwable;
use Toolkit\PFlag\FlagsParser;
use function array_unshift;

/**
 * Class UpdateNoPushCmd
 *
 * @package Inhere\Kite\Console\Controller\Gitlab
 */
class UpdateNoPushCmd extends Command
{
    protected static string $name = 'update';
    protected static string $desc = 'update codes from origin and main remote repositories';

    public static function aliases(): array
    {
        return ['up'];
    }

    protected function configFlags(FlagsParser $fs): void
    {
        // $this->flags->addOptByRule($name, $rule);
    }

    /**
     * update codes from origin and main remote repository, then push to remote
     *
     * @options
     *  --dr, --dry-run             bool;Dry-run the workflow, dont real execute
     *  --rb, --remote-branch       The remote branch name, default is current branch name.
     *
     * @param Input $input
     * @param Output $output
     *
     * @return int
     * @throws Throwable
     */
    protected function execute(Input $input, Output $output): int
    {
        $flags = $this->flags->getFlags();
        array_unshift($flags, '--np');

        $upCmd = new UpdatePushCmd($input, $output);
        $upCmd->setParent($this->getParent());
        $upCmd->run($flags);

        return 0;
    }
}
