<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Command;

use Inhere\Console\Command;
use Inhere\Console\Exception\ConsoleException;
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Helper\SysCmd;
use function is_array;
use function is_string;
use function strpos;
use function strtr;

/**
 * Class RunCommand
 */
class RunCommand extends Command
{
    protected static $name = 'run';

    protected static $description = 'run an script command in the .kite.inc';

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

        $name = $input->getFirstArg();

        $showList = $input->getSameOpt(['l', 'list'], false);
        if ($showList) {
            $listOpt = [
                'ucFirst' => false,
            ];

            if ($name && isset($scripts[$name])) {
                $output->aList([
                    'name'    => $name,
                    'command' => $scripts[$name],
                ], 'script information', $listOpt);
            } else {
                $output->aList($scripts, 'registered scripts', $listOpt);
            }

            return;
        }

        if (!$name) {
            $output->liteError('please input an script name for run');
            return;
        }

        if (!isset($scripts[$name])) {
            $output->liteError("please input an exists script name for run. ('{$name}' not exists)");
            return;
        }

        $runArgs = $input->getArguments();
        // first is script name
        unset($runArgs[0]);

        // script commands
        $commands = $scripts[$name];
        if (is_string($commands)) {
            // Color::println("run > $commands", 'comment');
            // Sys::execute($commands, false);
            $ret = SysCmd::exec($this->replaceScriptVars($commands, $runArgs));
            echo $ret['output'];
            return;
        }

        if (is_array($commands)) {
            foreach ($commands as $index => $command) {
                if (!$command) {
                    $output->liteError("The script {$name}.{$index} command is empty, skip run it");
                    continue;
                }

                if (!is_string($command)) {
                    $output->liteError("The script {$name}.{$index} command is not string, skip run it");
                    continue;
                }

                // Color::println("run > $command", 'comment');
                // Sys::execute($command, false);
                $ret = SysCmd::exec($this->replaceScriptVars($command, $runArgs));
                echo $ret['output'];
            }
            return;
        }

        $output->error("invalid script commands for '{$name}', only allow: string, string[]");
    }

    /**
     * @param string $cmdString
     * @param array  $scriptArgs
     *
     * @return string
     */
    private function replaceScriptVars(string $cmdString, array $scriptArgs): string
    {
        if (strpos($cmdString, '$') === false) {
            return $cmdString;
        }

        if (!$scriptArgs) {
            throw new PromptException('missing arguments for run script');
        }

        $pairs = [];

        // like bash script, first var is '$1'
        foreach ($scriptArgs as $i => $arg) {
            $pairs['$' . $i] = $arg;
        }

        return strtr($cmdString, $pairs);
    }
}
