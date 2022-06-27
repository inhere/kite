<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\Gitflow;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use PhpGit\Repo;
use Toolkit\Stdlib\Str;
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
        if ($fs->getOpt('all')) {
            $opts['all'] = true;
        } elseif ($remote = $fs->getOpt('remote')) {
            $opts['remotes'] = true;
        }

        $list = $repo->getGit()->branch->getList($opts);

        $onlyName = $fs->getOpt('only-name');
        $keyword  = $fs->getOpt('search');
        $keyword  = strlen($keyword) > 1 ? $keyword : '';

        $msg = 'Branch List';
        if (strlen($remote) > 1) {
            $msg .= " Of '$remote'";
        }

        $exclude = '';
        if ($keyword) {
            $msg .= "(keyword: $keyword)";
            if ($keyword[0] === '^') {
                $exclude = Str::splitTrimmed(substr($keyword, 1));
                $keyword = '';
            } else {
                $keyword = Str::splitTrimmed($keyword);
            }
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

            // 排除匹配
            if ($exclude) {
                if (Str::has($name, $exclude)) {
                    continue;
                }

                // 包含匹配搜索
            } elseif ($keyword && !Str::has($name, $keyword)) {
                continue;
            }

            $matched[$name] = $item;
        }

        // \vdump($keyword, $remote, $list);
        if ($inline) {
            $output->println(implode(',', array_keys($matched)));
        } elseif ($onlyName) {
            $output->println(array_keys($matched));
        } else {
            $output->table($matched, 'Git Branches');
        }
    }
}
