<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitlabCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Common\GitLocal\AbstractGitx;
use Inhere\Kite\Common\GitLocal\GitFactory;
use PhpGit\Info\BranchInfos;
use Toolkit\Stdlib\Helper\Assert;
use function date;
use function preg_replace_callback;
use function str_contains;

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

    private ?AbstractGitx $gx = null;

    public static function aliases(): array
    {
        return ['new', 'n'];
    }

    protected function configure(): void
    {
        $this->gx = GitFactory::make();

        $this->forkRemote = $this->gx->getForkRemote();
        $this->mainRemote = $this->gx->getMainRemote();

        if (!$this->forkRemote || !$this->mainRemote) {
            $this->output->liteError('missing config for "forkRemote" and "mainRemote" on "git"');
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
     *  --nm, --not-main        bool;Dont push new branch to the main remote
     *  --dry, --dry-run        bool;Dry-run the workflow, dont real execute
     *
     * @arguments
     *  branch      string;The new branch name, allow var: {ymd};required
     *
     * @param Input $input
     * @param Output $output
     *
     * @return mixed
     * @example
     *   {binWithCmd} fea_220612
     *   {binWithCmd} fix_{ymd}
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

        $brName = $fs->getArg('branch');
        if (str_contains($brName, '{')) {
            $brName = preg_replace_callback('/{([\w-]+)}/', static fn(array $m) => date($m[1]) ?: $m[0], $brName);

            Assert::isFalse(str_contains($brName, '{'), 'invalid name var for create branch');
        }

        $output->info('fetch latest information from remote: ' . $this->mainRemote);
        $repo = $this->gx->getRepo();
        $repo->gitCmd('fetch', $this->mainRemote, '-np')->runAndPrint();

        $bs = $repo->getBranchInfos();
        if ($bs->hasBranch($brName, BranchInfos::FROM_ALL)) {
            $output->warning("Branch '$brName' has been exists, please use checkout to switch");
            return 0;
        }

        $notToMain = $fs->getOpt('not-main');
        // $dryRun = true;
        $dryRun = $fs->getOpt('dry-run');
        $defBr  = $this->gx->getDefaultBranch();

        $cmd = CmdRunner::new()
            ->addf('git checkout %s', $defBr)
            ->addf('git pull %s %s', $this->mainRemote, $defBr)
            ->add('git push') // update to origin
            ->addf('git checkout -b %s', $brName)
            ->addf('git push -u %s %s', $this->forkRemote, $brName)
            ->addWheref(static function () use ($notToMain) {
                return $notToMain === false;
            }, 'git push %s %s', $this->mainRemote, $brName)
            // ->addf('git push %s %s', $this->mainRemote, $newBranch)
            ->setDryRun($dryRun)
            ->run(true);

        if ($cmd->isSuccess()) {
            // $output->error($cmd->getOutput() ?: 'Failure');
            $output->success('Complete: ' . date('Y-m-d H:i:s'));
        }

        return 0;
    }
}
