<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Helper;

use Inhere\Kite\Common\CmdRunner;
use Toolkit\Cli\Color;
use Toolkit\Sys\Sys;
use function sprintf;
use function trim;

/**
 * Class GitUtil
 *
 * get tag: git describe --tags --exact-match HEAD
 * get branch: git branch -a | grep "*"
 *
 * @package Inhere\Kite\Helper
 */
class GitUtil
{
    /**
     * @param string $workDir
     *
     * @return string
     */
    public static function getCurrentBranchName(string $workDir = ''): string
    {
        $str = 'git branch --show-current';
        $cmd = CmdRunner::new($str, $workDir);

        return $cmd->do()->getOutput(true);
    }

    /**
     * @param string $workDir
     *
     * @return string
     */
    public static function getLatestCommitId(string $workDir = ''): string
    {
        // latest commit id by: git log --pretty=%H -n1 HEAD
        $str = 'git log --pretty=%H -n1 HEAD';
        $cmd = CmdRunner::new($str, $workDir);

        return $cmd->do()->getOutput(true);
    }

    /**
     * @param string $message
     */
    public static function commit(string $message): void
    {
        $ret = SysCmd::exec(sprintf('git add . && git commit -m "%s"', $message));

        echo $ret['output'] . PHP_EOL;
    }

    /**
     * @param string $workDir
     * @param bool   $showInfo
     *
     * @return string
     */
    public static function findTag(string $workDir = '', bool $showInfo = false): string
    {
        $cmd = 'git describe --tags $(git rev-list --tags --max-count=1)';

        if ($showInfo) {
            Color::printf("Info:\n  Command <info>%s</info>\n  WorkDir <info>%s</info>\n", $cmd, $workDir);
        }

        [$code, $tagName,] = Sys::run($cmd, $workDir);
        if ($code !== 0) {
            return '';
        }

        return trim($tagName);
    }

    /**
     * @param string $remote
     * @param string $tag
     * @param string $workDir
     */
    public static function delRemoteTag(string $remote, string $tag, string $workDir = ''): void
    {
        $cmd = "git push $remote :refs/tags/$tag";
        $ret = SysCmd::exec($cmd, $workDir);

        echo $ret['output'] . PHP_EOL;
    }
}
