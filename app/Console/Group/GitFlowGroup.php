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

    public static function aliases(): array
    {
        return ['git-flow', 'gf'];
    }

    protected function configure(): void
    {
        $action = $this->getAction();

        if ($action === 'sync') {
            $this->curBranchName = GitUtil::getCurrentBranchName();

            $this->addCommentsVar('curBranchName', $this->curBranchName);
        }
    }

    /**
     * @return bool
     */
    protected function beforeExecute(): bool
    {
        $this->config = $this->app->getParam('gitflow', []);

        return true;
    }

    /**
     * sync codes from remote main repo
     *
     * @options
     *  -b, --branch  The sync code branch name, default is current branch
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
        $pwd  = $input->getPwd();
        $info = [
            'Work Dir' => $pwd,
        ];
        $output->aList($info, 'Work Information');

        if (!$curBranch = $this->curBranchName) {
            $curBranch = GitUtil::getCurrentBranchName();
        }

        $forkRemote = $this->config['fork']['remote'];
        $mainRemote = $this->config['main']['remote'];

        $remotes = GitUtil::getRemotes($pwd);
        if (!isset($remotes[$mainRemote])) {
            $names = array_keys($remotes);

            $output->liteError("The remote '{$mainRemote}' is not exists. remotes: ", implode(',', $names));
            return;
        }

        // git pull main BRANCH
        $str = "git pull {$mainRemote} $curBranch";
        $cmd = CmdRunner::new($str);
        $cmd->do();

        $output->info('Complete');
    }
}
