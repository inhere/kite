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

    /**
     * Method to execute a command in the system
     * Uses :
     * 1. system
     * 2. passthru
     * 3. exec
     * 4. shell_exec
     *
     * @param string $command
     * @param string $workDir
     * @param bool   $returnStatus
     *
     * @return array|string
     */
    public static function exec2(string $command, string $workDir = '', bool $returnStatus = true)
    {
        if ($workDir) {
            chdir($workDir);
        }

        // system
        $status = 1;
        if (function_exists('system')) {
            ob_start();
            system($command, $status);
            $output = ob_get_clean();

            // passthru
        } elseif (function_exists('passthru')) {
            ob_start();
            passthru($command, $status);
            $output = ob_get_clean();
            // exec
        } elseif (function_exists('exec')) {
            exec($command, $outputs, $status);
            $output = implode("\n", $outputs);

            // shell_exec
        } elseif (function_exists('shell_exec')) {
            $output = shell_exec($command);
        } else {
            $output = 'Command execution not possible on this system';
        }

        if ($returnStatus) {
            return [
                'code'   => $status,
                'output' => trim($output),
            ];
        }

        return trim($output);
    }

    /**
     * @param string $cmd
     * @param string $workDir
     * @param bool   $coRun
     */
    public static function execAndPrintResult(string $cmd, string $workDir = '', bool $coRun = false): void
    {
        $ret = self::exec($cmd, $workDir, $coRun);

        echo $ret['output'];
    }
}
