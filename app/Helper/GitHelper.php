<?php declare(strict_types=1);

namespace Inhere\PTool\Helper;

use Toolkit\Cli\Color;
use Toolkit\Sys\Sys;
use function is_dir;
use function sprintf;
use function trim;

class GitHelper
{
    /**
     * @param string $message
     */
    public static function commit(string $message): void
    {
        $ret = SysCmd::exec(sprintf('git add . && git commit -m "%s"', $message));

        if ($ret['code'] === 0) {
            echo $ret['output'] . PHP_EOL;
        }
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
}
