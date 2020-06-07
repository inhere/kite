<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Group;

use Inhere\Console\Controller;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Helper\GitUtil;
use function array_keys;
use function implode;

/**
 * Class GitFlowGroup
 */
class GitFlowGroup extends Controller
{
    protected static $name = 'gitflow';

    protected static $description = 'Some useful tool commands for git flow development';

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var string
     */
    private $curBranchName = '';

    /**
     * @var string
     */
    private $forkRemote = '';

    /**
     * @var string
     */
    private $mainRemote = '';

    public static function aliases(): array
    {
        return ['gf'];
    }

    protected function configure(): void
    {
        parent::configure();

        $this->config = $this->app->getParam('gitflow', []);

        $action = $this->getAction();

        if ($action === 'sync') {
            $this->curBranchName = GitUtil::getCurrentBranchName();

            $this->forkRemote = $this->config['fork']['remote'] ?? '';
            $this->mainRemote = $this->config['main']['remote'] ?? '';

            if (!$this->forkRemote || !$this->mainRemote) {
                $this->output->liteError('missing config for "fork.remote" and "main.remote" on "gitflow"');
                return;
            }

            $this->addCommentsVar('mainRemote', $this->mainRemote);
            $this->addCommentsVar('curBranchName', $this->curBranchName);
        }
    }

    /**
     * sync codes from remote main repo
     *
     * @options
     *  -b, --branch  The sync code branch name, default is current branch(<info>{curBranchName}</info>)
     *  -r, --remote  The main remote name, default: {mainRemote}
     *      --push    Push to origin remote after update
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {binWithCmd}             Sync code from the main repo remote {curBranchName} branch
     *  {binWithCmd} -b master   Sync code from the main repo remote master branch
     *
     */
    public function syncCommand(Input $input, Output $output): void
    {
        $forkRemote = $this->forkRemote;

        if (!$mainRemote = $input->getSameStringOpt(['r', 'remote'], '')) {
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

            $output->liteError("The remote '{$mainRemote}' is not exists. remotes: ", implode(',', $names));
            return;
        }

        // git pull main BRANCH
        $cmd = "git pull {$mainRemote} $curBranch";
        CmdRunner::new($cmd, $pwd)->do(true)->afterOkRun('git status');

        $output->success('Complete');
    }
}
