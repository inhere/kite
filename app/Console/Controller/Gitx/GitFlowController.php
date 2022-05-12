<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Controller\Gitx;

use Inhere\Console\Controller;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Console\SubCmd\Gitflow\BranchCreateCmd;
use Inhere\Kite\Helper\GitUtil;
use Inhere\Kite\Kite;
use Throwable;
use Toolkit\PFlag\FlagsParser;
use function array_keys;
use function implode;

/**
 * Class GitFlowGroup
 */
class GitFlowController extends Controller
{
    protected static string $name = 'gitflow';

    protected static string $desc = 'Some useful tool commands for git flow development';

    /**
     * @var string
     */
    private string $curBranchName = '';

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
        return ['gf'];
    }

    protected static function commandAliases(): array
    {
        return [
            'newBranch'    => ['new-br', 'newbr', 'nbr', 'nb'],
        ];
    }

    // protected function subCommands()
    // {
    //     return [];
    // }

    /**
     * @return string[]
     */
    protected function getOptions(): array
    {
        return [
            '--dry-run' => 'bool;Dry-run the workflow, dont real execute',
            '-y, --yes' => 'bool;Direct execution without confirmation',
            // '-i, --interactive' => 'Run in an interactive environment[TODO]',
        ];
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

        // $action === 'sync'
        // $action = $this->getAction();
    }

    /**
     * checkout an new branch for development
     *
     * @options
     *  --not-main   bool;Dont push new branch to the main remote
     *  --dry-run    bool;Dry-run the workflow, dont real execute
     *
     * @arguments
     *  branch      string;The new branch name. eg: fea_210612;required
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @throws Throwable
     * @example
     * Workflow:
     *  1. git checkout to master
     *  2. git pull <info>{mainRemote}</info> master
     *  3. git checkout -b NEW_BRANCH
     *  4. git push -u <info>{forkRemote}</info> NEW_BRANCH
     *  5. git push <info>{mainRemote}</info> NEW_BRANCH
     */
    public function newBranchCommand(FlagsParser $fs, Output $output): void
    {
        $bcCmd = new BranchCreateCmd($this->input, $output);
        $bcCmd->run($fs->getFlags());
    }

    /**
     * Resolve git conflicts
     *
     * @param Input  $input
     * @param Output $output
     */
    public function resolveCommand(Input $input, Output $output): void
    {
        // curBranch, tgtBranch
        // git checkout TGT_BRANCH
        // git pull && git push
        // git pull main TGT_BRANCH
        // git merge CUR_BRANCH
        // resolve conflicts
        // git add . && git ci
        // $branch = $input->getRequiredArg('branch');

        $output->success('TODO');
    }

    protected function syncConfigure(): void
    {
        $this->curBranchName = GitUtil::getCurrentBranchName();

        $this->addCommentsVar('mainRemote', $this->mainRemote);
        $this->addCommentsVar('curBranchName', $this->curBranchName);
    }

    /**
     * sync codes from remote main repo
     *
     * @options
     *  -b, --branch    The sync code branch name, default is current branch(<info>{curBranchName}</info>)
     *  -r, --remote    The main remote name, default: {mainRemote}
     *      --push      bool;Push to origin remote after update
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {binWithCmd}             Sync code from the main repo remote {curBranchName} branch
     *  {binWithCmd} -b master   Sync code from the main repo remote master branch
     *
     */
    public function syncCommand(FlagsParser $fs, Input $input, Output $output): void
    {
        $forkRemote = $this->forkRemote;
        if (!$mainRemote = $fs->getOpt('remote')) {
            $mainRemote = $this->mainRemote;
        }

        if (!$forkRemote || !$mainRemote) {
            $output->liteError('missing config for "fork.remote" and "main.remote" on "gitflow"');
            return;
        }

        $pwd  = $input->getPwd();
        $info = [
            'Work Dir'    => $pwd,
            'Cur Branch'  => $this->curBranchName,
            'Fork Remote' => $forkRemote,
            'Main Remote' => $mainRemote,
        ];

        $output->aList($info, 'Work Information', ['ucFirst' => false]);

        if (!$curBranch = $this->curBranchName) {
            $curBranch = GitUtil::getCurrentBranchName();
        }

        $remotes = GitUtil::getRemotes($pwd);
        if (!isset($remotes[$mainRemote])) {
            $names = array_keys($remotes);
            $output->liteError("The remote '$mainRemote' is not exists. remotes: ", implode(',', $names));
            return;
        }

        // git pull main BRANCH
        $cmd = "git pull $mainRemote $curBranch";
        CmdRunner::new($cmd, $pwd)->do(true)->afterOkDo('git status');

        $output->success('Complete');
    }
}
