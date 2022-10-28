<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\Gitflow;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Common\GitLocal\GitFactory;
use Toolkit\PFlag\FlagsParser;
use function date;

/**
 * Class UpdatePushCmd
 *
 * @package Inhere\Kite\Console\Controller\Gitlab
 */
class UpdatePushCmd extends Command
{
    protected static string $name = 'up-push';
    protected static string $desc = 'quick delete git branches from local, origin, main remote';

    public static function aliases(): array
    {
        return ['upp'];
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
     *  --np, --not-push            bool;Not push to remote repo after updated.
     *  --rb, --remote-branch       The remote branch name, default is current branch name.
     *
     * @param Input $input
     * @param Output $output
     *
     * @return mixed
     */
    protected function execute(Input $input, Output $output): mixed
    {
        $typGit = GitFactory::make();

        $curBranch = $typGit->getCurBranch();
        $upBranch = $this->flags->getOpt('remote-branch', $curBranch);
        $output->info("Current Branch: $curBranch, fetch remote branch: $upBranch");

        $runner = CmdRunner::new();
        $runner->setDryRun($this->flags->getOpt('dry-run'));
        $runner->add('git pull');
        $runner->addf('git pull %s %s', $typGit->getMainRemote(), $upBranch);

        $defBranch = $typGit->getDefaultBranch();
        if ($upBranch !== $defBranch) {
            $runner->addf('git pull %s %s', $typGit->getMainRemote(), $defBranch);
        }

        $si = $typGit->getStatusInfo();

        $runner->addf('git push %s', $si->upRemote);
        $runner->run(true);

        $output->success('Complete. datetime: ' . date('Y-m-d H:i:s'));
        return 0;
    }
}
