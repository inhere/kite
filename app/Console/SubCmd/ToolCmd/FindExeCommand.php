<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\ToolCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Toolkit\Sys\Sys;

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

    protected function configure(): void
    {
        $this->flags->addArgByRule('keywords', 'string;The keywords for search;true');
        // $this->flags->addOpt('show', 's', 'show the tool info', 'bool');
        // $this->flags->addOpt('list', 'l', 'list all can be installed tools', 'bool');
    }

    protected function execute(Input $input, Output $output)
    {
        $fs = $this->flags;

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
        $output->info('TODO');
    }
}
