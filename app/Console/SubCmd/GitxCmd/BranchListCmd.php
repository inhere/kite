<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitxCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use PhpGit\Repo;
use function array_keys;
use function implode;
use function strlen;
use function strpos;
use function substr;

/**
 * class BranchListCmd
 *
 * @author inhere
 * @date 2022/6/14
 */
class BranchListCmd extends Command
{
    protected static string $name = 'list';
    protected static string $desc = 'list git branches for project';

    public static function aliases(): array
    {
        return ['ls'];
    }

    /**
     * list branch by git branch
     *
     * @options
     *  -a, --all               bool;Display all branches
     *  -r, --remote            Display branches for the given remote
     *  --on, --only-name       bool;Only display branch name
     *      --inline            bool;Only display branch name and print inline
     *  -s, --search            The keyword name for search branches, allow multi by comma.
     *                          Start with ^ for exclude.
     *
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $opts = [];
        $fs = $this->flags;
        $repo = Repo::new();

        $remote = '';
        $inline = $fs->getOpt('inline');
        $search = $fs->getOpt('search');

        if ($fs->getOpt('all')) {
            $opts['all'] = true;
        } elseif ($remote = $fs->getOpt('remote')) {
            $opts['remotes'] = true;
        }

        $list = $repo->getGit()->branch->getList($opts, $search);

        $msg = 'Branch List';
        if (strlen($remote) > 1) {
            $msg .= " Of '$remote'";
        }

        if (strlen($search) > 1) {
            $msg .= "(keyword: $search)";
        }

        $output->colored($msg . ':');

        $matched = [];
        $rmtLen  = strlen($remote) + 1;
        foreach ($list as $name => $item) {
            if ($remote) {
                $pos = strpos($name, $remote . '/');
                if ($pos !== 0) {
                    continue;
                }

                $name = substr($name, $rmtLen);
            }

            $matched[$name] = $item;
        }

        // \vdump($keyword, $remote, $list);
        if ($inline) {
            $output->println(implode(',', array_keys($matched)));
        } elseif ($fs->getOpt('only-name')) {
            $output->println(array_keys($matched));
        } else {
            $output->table($matched, 'Git Branches');
        }
    }
}
