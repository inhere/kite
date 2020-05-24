<?php declare(strict_types=1);
/**
 * This file is part of PTool.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\PTool\Helper;

use Toolkit\Cli\Color;
use Toolkit\Sys\Sys;
use function is_dir;
use function sprintf;
use function trim;

/**
 * Class GitUtil
 *
 * @package Inhere\PTool\Helper
 */
class GitUtil
{
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
    public static function findTag(string $workDir, bool $showInfo = false): string
    {
        if (!is_dir($workDir)) {
            return '';
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
