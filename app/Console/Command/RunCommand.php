<?php declare(strict_types=1);
/**
 * This file is part of PTool.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\PTool\Console\Command;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\PTool\Helper\SysCmd;
use function is_array;
use function is_string;

/**
 * Class RunCommand
 */
class RunCommand extends Command
{
    protected static $name = 'run';

    protected static $description = 'run an script command in the .ptool.inc';

    /**
     * @return string[]
     */
    public static function aliases(): array
    {
        return ['exec', 'script'];
    }

    /**
     * Do execute
     *
     * @options
     *  -l, --list  List all script names
     *
     * @param Input  $input
     * @param Output $output
     */
    protected function execute($input, $output)
    {
        $scripts = $this->app->getParam('scripts', []);
        if (!$scripts) {
            $output->write('no any scripts in the config');
            return;
        }

        $showList = $input->getSameOpt(['l', 'list'], false);
        if ($showList) {
            $output->aList($scripts, 'registered scripts', [
                'ucFirst' => false,
            ]);
            return;
        }

        if (!$name = $input->getFirstArg()) {
            $output->liteError('please input an script name for run');
            return;
        }

        if (!isset($scripts[$name])) {
            $output->liteError('please input an exists script name for run');
            return;
        }

        $commands = $scripts[$name];
        if (is_string($commands)) {
            // Color::println("run > $commands", 'comment');
            // Sys::execute($commands, false);
            $ret = SysCmd::exec($commands);
            echo $ret['output'];
            return;
        }

        if (is_array($commands)) {
            foreach ($commands as $index => $command) {
                if (!is_string($command)) {
                    $output->liteError("The {$name}.{$index} command is not string, skip run");
                    continue;
                }

                // Color::println("run > $command", 'comment');
                // Sys::execute($command, false);
                $ret = SysCmd::exec($command);
                echo $ret['output'];
            }
            return;
        }

        $output->error("invalid script commands for '{$name}', only allow: string, string[]");
    }
}
