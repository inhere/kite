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

    public static function aliases(): array
    {
        return ['git-flow', 'gf'];
    }

    protected function configure(): void
    {
        $action = $this->getAction();

        if ($action === 'sync') {
            $this->addCommentsVar('curBranchName', GitUtil::getCurrentBranchName());
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
     * @param Input $input
     * @param Output $output
     *
     * @example
     *  {binWithCmd}          Sync code from the main repo remote {curBranchName} branch
     *  {binWithCmd} master   Sync code from the main repo remote master branch
     *
     */
    public function syncCommand(Input $input, Output $output): void
    {
        $output->aList([
            'Work Dir' => $input->getPwd(),
        ], 'Work Information');

        $curBranch = GitUtil::getCurrentBranchName();

        $forkRemote = $this->config['fork']['remote'];
        $mainRemote = $this->config['main']['remote'];

        // git pull main BRANCH
        $str = "git pull {$mainRemote} $curBranch";
        $cmd = CmdRunner::new($str);
        $cmd->do();

        $output->info('Complete');
    }
}
