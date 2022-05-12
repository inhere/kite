<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\Gitflow;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Kite;

/**
 * Class BranchCreateCmd
 *
 * @package Inhere\Kite\Console\Controller\Gitlab
 */
class BranchCreateCmd extends Command
{
    protected static string $name = 'create';
    protected static string $desc = 'create a new branch for git project';

    /**
     * @var string
     */
    private string $forkRemote = '';

    /**
     * @var string
     */
    private string $mainRemote = '';

    public static function aliases(): array
    {
        return ['new', 'n'];
    }

    protected function configure(): void
    {
        $this->initParams(Kite::config()->getArray('gitflow'));

        $this->forkRemote = $this->params->getString('forkRemote');
        $this->mainRemote = $this->params->getString('mainRemote');

        if (!$this->forkRemote || !$this->mainRemote) {
            $this->output->liteError('missing config for "forkRemote" and "mainRemote" on "gitflow"');
            return;
        }

        $this->addCommentsVars([
            'forkRemote' => $this->forkRemote,
            'mainRemote' => $this->mainRemote,
        ]);

        parent::configure();
    }

    /**
     * checkout an new branch for development
     *
     * @options
     *  --nm, --not-main    bool;Dont push new branch to the main remote
     *  --dry-run           bool;Dry-run the workflow, dont real execute
     *
     * @arguments
     *  branch      string;The new branch name. eg: fea_6_12;required
     *
     * @param Input $input
     * @param Output $output
     *
     * @return mixed
     * @example
     * Workflow:
     *  1. git checkout to master
     *  2. git pull <info>{mainRemote}</info> master
     *  3. git checkout -b NEW_BRANCH
     *  4. git push -u <info>{forkRemote}</info> NEW_BRANCH
     *  5. git push <info>{mainRemote}</info> NEW_BRANCH
     */
    protected function execute(Input $input, Output $output): mixed
    {
        $fs = $this->flags;

        $notToMain = $fs->getOpt('not-main');
        $newBranch = $fs->getArg('branch');

        // $cmd = CmdRunner::new('git checkout master')->do(true);
        // $cmd->afterOkRun("git pull {$this->mainRemote} master")
        //     ->afterOkRun("git checkout -b {$newBranch}")
        //     ->afterOkRun("git push -u {$this->forkRemote} {$newBranch}")
        //     ->afterOkRun("git push {$this->mainRemote} {$newBranch}");

        // $dryRun = true;
        $dryRun = $fs->getOpt('dry-run');

        $cmd = CmdRunner::new()
            ->add('git checkout master')
            ->addf('git pull %s master', $this->mainRemote)
            ->add('git push') // update to origin
            ->addf('git checkout -b %s', $newBranch)
            ->addf('git push -u %s %s', $this->forkRemote, $newBranch)
            ->addWheref(static function () use ($notToMain) {
                return $notToMain === false;
            }, 'git push %s %s', $this->mainRemote, $newBranch)
            // ->addf('git push %s %s', $this->mainRemote, $newBranch)
            ->setDryRun($dryRun)
            ->run(true);

        if ($cmd->isSuccess()) {
            // $output->error($cmd->getOutput() ?: 'Failure');
            $output->success('Complete');
        }

        return 0;
    }
}
