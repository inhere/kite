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
use function array_filter;
use function explode;
use function sprintf;
use function str_replace;
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
     * @param string $workDir
     *
     * @return array
     */
    public static function getRemotes(string $workDir = ''): array
    {
        // git remote -v
        $cmd = 'git remote -v';
        $run = CmdRunner::new($cmd, $workDir);
        $out = $run->do()->getOutput(true);

        if (!$out) {
            return [];
        }

        // parse
        $lines = explode("\n", $out);

        $remotes = [];
        foreach ($lines as $line) {
            // format
            $line = str_replace("\t", ' ', $line);
            // parse
            [$name, $url] = array_filter(explode(' ', trim($line)));
            // add
            $remotes[$name] = $url;
        }

        return $remotes;
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
        // fix: update tags to latest
        $ret = SysCmd::exec('git pull --tags', $workDir);
        if ($showInfo) {
            echo $ret['output'] . PHP_EOL;
        }

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
