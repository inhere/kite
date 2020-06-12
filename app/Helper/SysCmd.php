<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Helper;

use Swoole\Coroutine;
use Toolkit\Cli\Color;
use Toolkit\Sys\Sys;
use function chdir;
use function exec;
use function function_exists;
use function implode;
use function ob_get_clean;
use function ob_start;
use function passthru;
use function shell_exec;
use function system;
use function trim;

/**
 * Class SysCmd
 *
 * @package Inhere\Kite\Helper
 */
class SysCmd
{
    /**
     * @param string $cmd
     * @param string $workDir
     * @param bool   $coRun
     *
     * @return array
     */
    public static function exec(string $cmd, string $workDir = '', bool $coRun = false): array
    {
        Color::println("> $cmd", 'yellow');

        if ($coRun) {
            $ret = Coroutine::exec($cmd);
            if ((int)$ret['code'] !== 0) {
                $msg = "Exec command error. Output: {$ret['output']}";
                Color::println($msg, 'error');
            }

            return $ret;
        }

        // normal run
        [$code, $output,] = Sys::run($cmd, $workDir);
        if ($code !== 0) {
            $msg = "Exec command error. Output: {$output}";
            Color::println($msg, 'error');
        }

        return [
            'code'   => $code,
            'output' => $output,
        ];
    }
}
