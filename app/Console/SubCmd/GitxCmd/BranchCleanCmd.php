<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitxCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use PhpGit\Repo;

/**
 * class BranchCleanCmd
 *
 * @author inhere
 * @date 2023/1/9
 */
class BranchCleanCmd extends Command
{
    protected static string $name = 'clean';
    protected static string $desc = 'quickly clean git branches by input conditions';

    public static function aliases(): array
    {
        return ['clear', 'clr'];
    }

    /**
     * @options
     *  -a, --all               bool;Display branches for all remote and local
     *  -r, --remote            Both delete local and remote branches
     *  -s, --search            The keywords for search branches, allow multi by comma.
     *                          Start with ^ for exclude.
     *  --m, --match            The branch name search match, support var and regex.
     *                          eg: fix_{ymd}, fix_{ymd}*, fix_[\d]{6}.
     *  --cond, --condition     The condition for clean handle logic.
     *                          eg: $ymd < 180Day
     *
     * @param Input  $input
     * @param Output $output
     * @example
     *   {binWithCmd} -m fix_{ymd} --cond '$ymd < 180Day'
     */
    protected function execute(Input $input, Output $output): void
    {
        $fs = $this->flags;
        $opts = [];
        $repo = Repo::new();

        $remote = '';
        $search = $fs->getOpt('search');

        if ($fs->getOpt('all')) {
            $opts['all'] = true;
        } elseif ($remote = $fs->getOpt('remote')) {
            $opts['remotes'] = true;
        }

        $list = $repo->getGit()->branch->getList($opts, $search);

        foreach ($list as $item) {
            $output->println("branch: {$item['name']}");
        }
    }
}
