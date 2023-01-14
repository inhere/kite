<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\ToolCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Sys\Sys;
use function array_merge;
use function file_exists;
use function glob;

/**
 * class FindExeCommand
 *
 * @author inhere
 */
class FindExeCommand extends Command
{
    protected static string $name = 'find-exe';
    protected static string $desc = 'search executable file in system PATH dirs.';

    /**
     * @return string[]
     */
    public static function aliases(): array
    {
        return ['find-bin'];
    }

    protected function configFlags(FlagsParser $fs): void
    {
        $fs->addOpt('match', 'm', 'use name for like search', 'bool');
        $fs->addOpt('verbose', 'v', 'display more information', 'bool');
        $fs->addArgByRule('name', 'string;The executable name for find;true');
    }

    protected function execute(Input $input, Output $output)
    {
        $fs = $this->flags;

        $more = $fs->getOpt('verbose');
        $like = $fs->getOpt('match');

        $paths = Sys::getEnvPaths();
        $name  = $fs->getArg('name');
        $more && $output->aList($paths, "ENV PATH");

        $result = [];
        foreach ($paths as $path) {
            if (!$like) {
                if (file_exists($binFile = $path . "/$name")) {
                    $output->println($more ? "RESULT: $binFile" : $binFile);
                    return;
                }
                continue;
            }

            $matches = glob($path . "/*$name*");
            if ($matches) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $result = array_merge($result, $matches);
            }
        }

        $output->aList($result, 'RESULT');
    }
}
