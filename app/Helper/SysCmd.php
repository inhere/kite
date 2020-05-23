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
use Swoole\Coroutine;
use Toolkit\Sys\Sys;

/**
 * Class SysCmd
 *
 * @package Inhere\PTool\Helper
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
