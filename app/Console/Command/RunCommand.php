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
use Inhere\Kite\Helper\SysCmd;
use Toolkit\Cli\Cli;
use Toolkit\FsUtil\File;
use function basename;
use function count;
use function explode;
use function in_array;
use function is_array;
use function is_file;
use function is_scalar;
use function is_string;
use function json_encode;
use function preg_match;
use function strpos;
use function strtr;
use function trim;

/**
 * Class RunCommand
 */
class RunCommand extends Command
{
    protected static $name = 'run';

    protected static $description = 'run an script command in the "scripts"';

    /**
     * @var bool
     */
    private $dryRun = false;

    /**
     * @var array
     */
    private $scripts = [];

    /**
     * @var array
     */
    private $scriptExts = ['.sh', '.bash', '.php'];

    /**
     * @var array
     */
    private $scriptDirs = [];

    /**
     * @return string[]
     */
    public static function aliases(): array
    {
        return ['exec', 'script'];
    }

    protected function beforeExecute(): bool
    {
        $this->scripts = $this->app->getParam('scripts', []);

        $this->scriptDirs = $this->app->getParam('scriptDirs', []);

        return parent::beforeExecute();
    }

    /**
     * Do execute
     *
     * @options
     *  -l, --list      List information for all scripts or one script
     *  -s, --search    Display all matched scripts by the input name
     *      --dry-run   Mock running an script
     *
     * @arguments
     *  name        The script name for execute
     *
     * @param Input  $input
     * @param Output $output
     */
    protected function execute($input, $output)
    {
        $name = $input->getFirstArg();
        if ($input->getSameOpt(['l', 'list'], false)) {
            $this->listScripts($output, $name);
            return;
        }

        // support search
        $kw = $input->getSameStringOpt(['s', 'search']) ?: $name;
        if ($input->hasOneOpt(['s', 'search'])) {
            $this->searchScripts($output, $kw);
            return;
        }

        if (!$name) {
            $output->liteError('please input an script name for run or use -l see all scripts');
            return;
        }

        // not found name
        if (!isset($this->scripts[$name])) {
            if ($scriptFile = $this->findScriptFile($name)) {
                $this->runScriptFile($output, $scriptFile);
                return;
            }

            $output->liteError("please input an exists script name for run. ('$name' not exists)");
            return;
        }

        $runArgs = $input->getArguments();
        // first is script name
        unset($runArgs[0]);

        // script commands
        $commands = $this->scripts[$name];

        // run scripts
        $this->dryRun = $input->getBoolOpt('dry-run');
        $this->executeScripts($output, $name, $runArgs, $commands);
    }

    /**
     * @param Output $output
     * @param string $name
     * @param array  $runArgs
     * @param mixed  $commands
     */
    private function executeScripts(Output $output, string $name, array $runArgs, $commands): void
    {
        if (is_string($commands)) {
            // bash -c "echo hello"
            // bash some.sh
            if ($scriptFile = $this->findScriptFile($commands)) {
                $this->runScriptFile($output, $scriptFile);
                return;
            }

            $command = $this->replaceScriptVars($name, $commands, $runArgs);
            // CmdRunner::new($command)->do(true);
            $this->executeScript($command, true);
            return;
        }

        if (is_array($commands)) {
            if (isset($commands['_meta'])) {
                unset($commands['_meta']);
            }

            foreach ($commands as $index => $command) {
                $pos = $name . '.' . $index;
                if (!$command) {
                    $output->liteError("The script $pos command is empty, skip run it");
                    continue;
                }

                if (!is_string($command)) {
                    $output->liteError("The script $pos command is not string, skip run it");
                    continue;
                }

                // bash -c "echo hello"
                // bash some.sh
                if ($scriptFile = $this->findScriptFile($command)) {
                    $this->runScriptFile($output, $scriptFile);
                    continue;
                }

                $command = $this->replaceScriptVars($name, $command, $runArgs);
                $this->executeScript($command);
            }
            return;
        }

        $output->error("invalid script commands for '$name', only allow: string, string[]");
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function findScriptFile(string $name): string
    {
        $ext = File::getExtension($name);
        if (!$ext || !in_array($ext, $this->scriptExts, true)) {
            return '';
        }

        if (is_file($name)) {
            return $name;
        }

        foreach ($this->scriptDirs as $scriptDir) {
            $relativeFile = $scriptDir . '/' . $name;
            if (is_file($relativeFile)) {
                return $relativeFile;
            }
        }

        return '';
    }

    /**
     * @param Output $output
     * @param string $scriptFile
     */
    private function runScriptFile(Output $output, string $scriptFile): void
    {
        // #!/usr/bin/env bash
        // #!/usr/bin/bash
        $line = File::readFirstLine($scriptFile);
        $name = basename($scriptFile);

        // must start withs '#!'
        if (!$line || strpos($line, '#!') !== 0) {
            $output->colored("will direct run the script file: $name", 'cyan');
            $this->executeScript($scriptFile);
            return;
        }

        $output->colored("will run the script file: $name (shebang: $line)", 'cyan');

        // eg: '#!/usr/bin/env bash'
        if (strpos($line, ' ') > 0) {
            [, $binName] = explode(' ', $line, 2);
        } else { // eg: '#!/usr/bin/bash'
            $binName = trim($line, '#!');
        }

        // eg: "bash hello.sh"
        $this->executeScript("$binName $scriptFile");
    }

    /**
     * @param string $command
     * @param bool   $onlyOne
     */
    private function executeScript(string $command, bool $onlyOne = false): void
    {
        // CmdRunner::new($command)->do(true);
        if ($this->dryRun) {
            Cli::colored('DRY-RUN: ' . $command, 'cyan');
        } else {
            SysCmd::quickExec($command);
        }

        if ($onlyOne) {
            Cli::println('');
        }

        Cli::colored("DONE:\n $command");
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
                throw new PromptException("missing arguments for run script '$name', detail: '$cmdString'");
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
