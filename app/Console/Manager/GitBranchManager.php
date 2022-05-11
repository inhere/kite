<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Manager;

use Inhere\Kite\Common\CmdRunner;

/**
 * Class GitBranchManage
 *
 * @package Inhere\Kite\Console\Manage
 */
class GitBranchManager extends BaseGitManage
{
    /**
     * update branch list from remote
     *
     * @param array $remotes
     * @param bool $usePull
     *
     * @return void
     */
    public function update(array $remotes = [], bool $usePull = false): void
    {
        if (!$remotes) {
            $remotes = [self::DEFAULT_REMOTE];
        }

        $run = CmdRunner::new();
        $cmd = $usePull ? 'pull' : 'fetch';
        foreach ($remotes as $remote) {
            $run->addf('git %s --prune %s', $cmd, $remote);
        }

        $run->runAndPrint();
    }
}
