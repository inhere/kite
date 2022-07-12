<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitlabCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Helper\AppHelper;

/**
 * class ResolveConflictCmd
 *
 * @author inhere
 * @date 2022/7/11
 */
class ResolveConflictCmd  extends Command
{
    protected static string $name = 'resolve';
    protected static string $desc = 'create a new branch for git project';

    public static function aliases(): array
    {
        return ['rc'];
    }

    /**
     * Resolve conflicts preparing for current git branch.
     *
     * @help
     *   1. will checkout to <cyan>branch</cyan>
     *   2. will update code by <cyan>git pull</cyan>
     *   3. update the <cyan>branch</cyan> codes from main repository
     *   4. merge current-branch codes from main repository
     *   5. please resolve conflicts by tools or manual
     *
     * @arguments
     *    branch    string;The conflicts target branch name. eg: testing, qa, pre;required
     *
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $fs = $this->flags;
        $gl = AppHelper::newGitlab();

        $branch = $fs->getArg('branch');
        $branch = $gl->getRealBranchName($branch);

        $dryRun = false;
        if ($p = $this->getParent()) {
            $dryRun = $p->getFlags()->getOpt('dry-run');
        }

        $curBranch = $gl->getCurBranch();
        // $orgRemote = $gl->getForkRemote();

        $runner = CmdRunner::new();
        $runner->setDryRun($dryRun);
        $runner->add('git fetch');
        $runner->addf('git checkout %s', $branch);
        // git checkout --track origin/BRANCH
        // $runner->addf('git checkout --track %s/%s', $orgRemote, $branch);
        $runner->addf('git pull');
        $runner->addf('git pull %s %s', $gl->getMainRemote(), $branch);
        $runner->addf('git pull %s %s', $gl->getMainRemote(), $curBranch);
        $runner->run(true);

        $output->success('Complete. please resolve conflicts by tools or manual');
        $output->note('can exec this command after resolved for quick commit:');
        $output->colored("  git add . && git commit && git push && kite gl pr -o head && git checkout $curBranch", 'mga');
    }
}
