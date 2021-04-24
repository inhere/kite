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
use function array_pop;
use function explode;
use function implode;
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
     * @param string $str
     * @return boolean
     */
    public static function isFullUrl(string $str): bool
    {
        if (strpos($str, 'http:') === 0) {
            return true;
        }

        if (strpos($str, 'https:') === 0) {
            return true;
        }

        if (strpos($str, 'git@') === 0) {
            return true;
        }

        return false;
    }

    /**
     * @param string $workDir
     *
     * @return string
     */
    public static function getCurrentBranchName(string $workDir = ''): string
    {
        // 1. git symbolic-ref --short -q HEAD
        // 2. git rev-parse --abbrev-ref HEA
        // 3. git branch --show-current // 老版本不支持
        $str = 'git symbolic-ref --short -q HEAD';
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
        SysCmd::quickExec(sprintf('git add . && git commit -m "%s"', $message));
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
        $ret = SysCmd::exec('git fetch --tags', $workDir);
        if ($showInfo) {
            echo $ret['output'] . PHP_EOL;
        }

        // $cmd = 'git describe --tags $(git rev-list --tags --max-count=1)';
        $cmd = 'git describe --abbrev=0 --tags';

        if ($showInfo) {
            Color::printf("Info:\n  Command <info>%s</info>\n  WorkDir <info>%s</info>\n", $cmd, $workDir);
        }

        [$code, $tagName, $error] = Sys::run($cmd, $workDir);
        if ($code !== 0) {
            echo $error;
            return '';
        }

        return trim($tagName);
    }

    /**
     * Get next tag version. eg: v2.0.3 => v2.0.4
     *
     * @param string $tagName
     *
     * @return string
     */
    public static function buildNextTag(string $tagName): string
    {
        $nodes = explode('.', $tagName);

        $lastNum = array_pop($nodes);
        $nodes[] = (int)$lastNum + 1;

        return implode('.', $nodes);
    }

    /**
     * @param string $remote
     * @param string $tag
     * @param string $workDir
     */
    public static function delRemoteTag(string $remote, string $tag, string $workDir = ''): void
    {
        $cmd = "git push $remote :refs/tags/$tag";

        SysCmd::quickExec($cmd, $workDir);
    }
}
