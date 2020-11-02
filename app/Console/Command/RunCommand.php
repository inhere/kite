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
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use function count;
use function is_array;
use function is_scalar;
use function is_string;
use function json_encode;
use function preg_match;
use function strpos;
use function strtr;

/**
 * Class RunCommand
 */
class RunCommand extends Command
{
    protected static $name = 'run';

    protected static $description = 'run an script command in the "scripts"';

    /**
     * @var array
     */
    private $scripts = [];

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
     *  -l, --list      List information for all scripts or one script
     *  -s, --search    Display all matched scripts by the input name
     *      --dry-run   Mock running an script
     * @arguments
     *  name        The script name for execute
     *
     * @param Input  $input
     * @param Output $output
     */
    protected function execute($input, $output)
    {
        $this->scripts = $this->app->getParam('scripts', []);
        if (!$this->scripts) {
            $output->write('no any scripts in the config');
            return;
        }

        $name = $input->getFirstArg();
        if ($input->getSameOpt(['l', 'list'], false)) {
            $this->listScripts($output, $name);
            return;
        }

        if (!$name) {
            $output->liteError('please input an script name for run');
            return;
        }

        // support search
        if ($input->getSameBoolOpt(['s', 'search'])) {
            $this->searchScripts($output, $name);
            return;
        }

        if (!isset($this->scripts[$name])) {
            $output->liteError("please input an exists script name for run. ('{$name}' not exists)");
            return;
        }

        $runArgs = $input->getArguments();
        // first is script name
        unset($runArgs[0]);

        // script commands
        $commands = $this->scripts[$name];

        // run scripts
        $this->executeScripts($output, $name, $runArgs, $commands);
    }

    /**
     * @param Output $output
     * @param string $name
     * @param array  $runArgs
     * @param mixed $commands
     */
    private function executeScripts(Output $output, string $name, array $runArgs, $commands): void
    {
        if (is_string($commands)) {
            // Color::println("run > $commands", 'comment');
            // Sys::execute($commands, false);
            $command = $this->replaceScriptVars($name, $commands, $runArgs);
            CmdRunner::new($command)->do(true);
            $output->info('DONE: ' . $command);
            return;
        }

        if (is_array($commands)) {
            foreach ($commands as $index => $command) {
                $pos = $name . '.' . $index;
                if (!$command) {
                    $output->liteError("The script {$pos} command is empty, skip run it");
                    continue;
                }

                if (!is_string($command)) {
                    $output->liteError("The script {$pos} command is not string, skip run it");
                    continue;
                }

                $command = $this->replaceScriptVars($name, $command, $runArgs);
                CmdRunner::new($command)->do(true);
                $output->info('DONE: ' . $command);
            }
            return;
        }

        $output->error("invalid script commands for '{$name}', only allow: string, string[]");
    }

    /**
     * @param Output $output
     * @param string $name
     */
    private function listScripts(Output $output, string $name): void
    {
        $listOpt = [
            'ucFirst' => false,
        ];

        if ($name && isset($this->scripts[$name])) {
            // $desc = '';
            $item = $this->scripts[$name];

            // [_meta => [desc, ]]
            if (is_array($item) && isset($item['_meta'])) {
                $meta = $item['_meta'];
                unset($item['_meta']);

                $desc = $meta['desc'] ?? '';
                if ($desc) {
                    $output->colored($desc . "\n", 'cyan');
                }
            }

            $output->aList([
                'name'    => $name,
                'command' => $item,
            ], 'script information', $listOpt);
        } else {
            $count = count($this->scripts);
            $output->aList($this->scripts, "registered scripts(total: $count)", $listOpt);
        }
    }

    /**
     * @param Output $output
     * @param string $kw
     */
    private function searchScripts(Output $output, string $kw): void
    {
        $listOpt = [
            'ucFirst' => false,
        ];

        $matched = [];
        foreach ($this->scripts as $name => $item) {
            if (strpos($name, $kw) !== false) {
                $matched[$name] = $item;
            } else {
                $itemString = is_scalar($item) ? (string)$item : json_encode($item);
                if (strpos($itemString, $kw) !== false) {
                    $matched[$name] = $item;
                }
            }
        }

        $count = count($matched);
        if ($count === 0) {
            $output->info(':( not found matched commands by keywords: ' . $kw);
            return;
        }

        $output->aList($matched, "matched scripts(total: $count)", $listOpt);
    }

    /**
     * @param string $name
     * @param string $cmdString
     * @param array  $scriptArgs
     *
     * @return string
     */
    private function replaceScriptVars(string $name, string $cmdString, array $scriptArgs): string
    {
        if (strpos($cmdString, '$') === false) {
            return $cmdString;
        }

        if (!$scriptArgs) {
            // has vars $@ $1 ... $3 ...
            $matches = [];
            preg_match('/\$[\d+|@]/', $cmdString, $matches);
            if ($matches) {
                // \vdump($cmdString, $matches);
                throw new PromptException("missing arguments for run script '{$name}', detail: '$cmdString'");
            }
        }

        $full  = implode(' ', $scriptArgs);
        $pairs = [
            '$@' => $full,
            '$?' => $full, // optional all vars
        ];

        // like bash script, first var is '$1'
        foreach ($scriptArgs as $i => $arg) {
            $pairs['$' . $i] = $arg;
        }

        return strtr($cmdString, $pairs);
    }
}
