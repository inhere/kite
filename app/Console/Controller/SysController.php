<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Controller;

use Inhere\Console\Controller;
use Inhere\Console\IO\Output;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Sys\Sys;
use function array_merge;
use function glob;

/**
 * Class SysController
 */
class SysController extends Controller
{
    protected static string $name = 'sys';

    protected static string $desc = 'Some useful tool commands for system';

    protected static function commandAliases(): array
    {
        return [
            'exeFind'    => ['find', 'find-bin', 'find-exe'],
        ];
    }

    /**
     * search executable file in system PATH dirs.
     *
     * @arguments
     * keywords     string;The keywords for search;true
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function exeFindCommand(FlagsParser $fs, Output $output): void
    {
        $words = $fs->getArg('keywords');
        $paths = Sys::getEnvPaths();
        $output->aList($paths, "ENV PATH");

        $result = [];
        foreach ($paths as $path) {
            $matches = glob($path. "/*$words*");
            if ($matches) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $result = array_merge($result, $matches);
            }
        }

        $output->aList($result, 'RESULT');
    }
}
