<?php declare(strict_types=1);

namespace Inhere\Kite\Common;

use RuntimeException;
use Toolkit\Cli\Color;
use Toolkit\Sys\Exec;
use function chdir;
use function is_array;
use function is_string;
use function sprintf;
use function system;
use function trim;

/**
 * Class SysCmdExec
 *
 * @package Inhere\Kite\Common
 */
class CmdRunner
{
    /**
     * @var string
     */
    private $command;

    /**
     * @var string
     */
    private $workDir;

    /**
     * @var int
     */
    private $code = 0;

    /**
     * @var string
     */
    private $error = '';

    /**
     * @var string
     */
    private $output = '';

    /**
     * [
     *  'name' => 'command line',
     *  'test' => [
     *      'echo hi',
     *      'do something',
     *  ]
     * ]
     *
     * @var array[]|array
     */
    private $commands = [];

    /**
     * Dry run all commands
     *
     * @var bool
     */
    private $dryRun = false;

    /**
     * @var bool
     */
    private $printCmd = true;

    /**
     * Ignore check prevision return code
     *
     * @var bool
     */
    private $ignoreError = false;

    /**
     * @var bool
     */
    private $printOutput = false;

    /**
     * @param string|array $cmd
     * @param string       $workDir
     *
     * @return static
     */
    public static function new($cmd = '', string $workDir = ''): self
    {
        return new self($cmd, $workDir);
    }

    /**
     * Class constructor.
     *
     * @param string|array $command One or multi commands
     * @param string       $workDir
     */
    public function __construct($command = null, string $workDir = '')
    {
        if (is_string($command)) {
            $this->command = $command;
        } elseif (is_array($command)) {
            $this->commands = (array)$command;
        }

        $this->workDir = $workDir;
    }

    /**
     * @param string $workDir
     *
     * @return $this
     */
    public function chDir(string $workDir): self
    {
        return $this->changeDir($workDir);
    }

    /**
     * @param string $workDir
     *
     * @return $this
     */
    public function changeDir(string $workDir): self
    {
        $this->workDir = $workDir;
        return $this;
    }

    /**************************************************************************
     * add ant run
     *************************************************************************/

    /**
     * @param bool $printOutput
     *
     * @return array
     */
    public function exec(bool $printOutput = false): array
    {
        $this->run($printOutput);

        return $this->getResult();
    }

    /**
     * @param bool $printOutput
     *
     * @return $this
     */
    public function do(bool $printOutput = false): self
    {
        return $this->run($printOutput);
    }

    /**
     * @param string $cmd
     *
     * @return $this
     */
    public function afterDo(string $cmd): self
    {
        return $this->setCommand($cmd)->do($this->printOutput);
    }

    /**
     * @param string   $cmd
     * @param callable $whereFunc
     *
     * @return $this
     */
    public function whereDo(string $cmd, callable $whereFunc): self
    {
        // only run on return TRUE
        if (true === $whereFunc()) {
            $this->setCommand($cmd)->do($this->printOutput);
        }

        return $this;
    }

    /**
     * @param string      $cmd
     * @param string|null $workDir
     *
     * @return $this
     */
    public function afterOkDo(string $cmd, string $workDir = null): self
    {
        if (0 !== $this->code) {
            return $this;
        }

        if ($workDir !== null) {
            $this->workDir = $workDir;
        }

        return $this->setCommand($cmd)->do($this->printOutput);
    }

    /**
     * @param string $command
     *
     * @return CmdRunner
     */
    public function setCommand(string $command): self
    {
        $this->command = $command;
        return $this;
    }

    /**************************************************************************
     * batch add then run
     *************************************************************************/

    /**
     * @param array $commands
     *
     * @return $this
     */
    public function batch(array $commands): self
    {
        $this->commands = $commands;

        return $this;
    }

    /**
     * @param string $cmdTpl
     * @param mixed  ...$args
     *
     * @return $this
     */
    public function addf(string $cmdTpl, ...$args): self
    {
        if ($args) {
            return $this->add(sprintf($cmdTpl, ...$args));
        }

        return $this->add($cmdTpl);
    }

    /**
     * @param callable $checker
     * @param string   $cmdTpl
     * @param mixed    ...$args
     *
     * @return $this
     */
    public function addWheref(callable $checker, string $cmdTpl, ...$args): self
    {
        return $this->addWhere($checker, sprintf($cmdTpl, ...$args));
    }

    /**
     * @param callable $checker
     * @param string   $command
     * @param string   $key
     *
     * @return $this
     */
    public function addWhere(callable $checker, string $command, string $key = ''): self
    {
        $item = [
            'where'   => $checker,
            'command' => $command,
        ];

        if ($key) {
            $this->commands[$key] = $item;
        } else {
            $this->commands[] = $item;
        }

        return $this;
    }

    /**
     * @param string $command
     * @param string $key
     *
     * @return $this
     */
    public function add(string $command, string $key = ''): self
    {
        if ($key) {
            $this->commands[$key] = $command;
        } else {
            $this->commands[] = $command;
        }

        return $this;
    }

    /**
     * @param array  $config
     *                     - command STRING|ARRAY
     *                     - workDir STRING
     *                     - where  callable
     * @param string $key
     *
     * @return $this
     */
    public function addByArray(array $config, string $key = ''): self
    {
        if (!isset($config['command'])) {
            throw new RuntimeException('must be setting "command" in the config');
        }

        if ($key) {
            $this->commands[$key] = $config;
        } else {
            $this->commands[] = $config;
        }

        return $this;
    }

    /**
     * run and print all output
     */
    public function runAndPrint(): void
    {
        $this->run(true);
    }

    /**
     * Run all added commands
     *
     * @param bool $printOutput
     *
     * @return $this
     */
    public function run(bool $printOutput = false): self
    {
        $this->printOutput = $printOutput;
        if ($command = $this->command) {
            $this->innerExecute($command, $this->workDir);

            // stop on error
            if (0 !== $this->code && false === $this->ignoreError) {
                Color::println("\nCommand exit code not equal to 0(code: {$this->code}), stop run.", 'red');
                return $this;
            }
        }

        if ($commands = $this->commands) {
            $this->runCommands($commands);
        }

        return $this;
    }

    /**
     * @param array $commands
     */
    private function runCommands(array $commands): void
    {
        Color::println('Starting Handle', 'suc');
        $step = 1;
        foreach ($commands as $command) {
            $workDir = $this->workDir;

            // see addWhere()
            if (is_array($command)) {
                $item = $command;

                $func = $item['where'] ?? '';
                if ($func && false === $func()) {
                    Color::println("Skip {$step} ...", 'cyan');
                    Color::println("- Does not meet the conditions", 'cyan');
                    continue;
                }

                $workDir = $item['workDir'] ?? $workDir;
                $command = $item['command'];
            }

            Color::println("STEP {$step}:", 'mga0');

            // custom work dir
            if ($workDir) {
                Color::println('- work dir is ' . $workDir, 'italic');
            }

            $this->innerExecute($command, $workDir);
            $step++;

            // stop on error
            if (0 !== $this->code && false === $this->ignoreError) {
                Color::println("\nCommand exit code not equal to 0(code: {$this->code}), stop run.", 'red');
                break;
            }
        }
    }

    /**************************************************************************
     * helper methods
     *************************************************************************/

    /**
     * @param string $command
     * @param string $workDir
     */
    protected function innerExecute(string $command, string $workDir): void
    {
        if (!$command) {
            throw new RuntimeException('The execute command cannot be empty');
        }

        if ($this->printCmd) {
            Color::println("> {$command}", 'yellow');
        }

        if ($this->dryRun) {
            $output = 'DRY-RUN: Command execute success';
            Color::println($output, 'cyan');
            return;
        }

        if ($this->printOutput) {
            $this->execAndPrint($command, $workDir);
        } else {
            $this->execLogReturn($command, $workDir);
        }
    }

    /**
     * exec and log outputs.
     *
     * @param string $command
     * @param string $workDir
     */
    protected function execLogReturn(string $command, string $workDir): void
    {
        [$code, $output, $error] = Exec::run($command, $workDir);

        // save output
        $this->code   = $code;
        $this->error  = trim($error);
        $this->output = trim($output);
        // if ($this->printOutput) {
        //     $hasOutput = false;
        //     if ($code !== 0 && $this->error) {
        //         $hasOutput = true;
        //         Color::println("error code $code:\n" . $this->error, 'red');
        //     }
        //
        //     $outMsg = '';
        //     if (false === $hasOutput) {
        //         $outMsg = $this->output ?: $this->error;
        //     } elseif ($this->output) {
        //         $outMsg = $this->output;
        //     }
        //
        //     if ($outMsg) {
        //         echo $outMsg . "\n";
        //     }
        // }
    }

    /**
     * direct print to stdout, not return outputs.
     *
     * @param string $command
     * @param string $workDir
     */
    protected function execAndPrint(string $command, string $workDir): void
    {
        // TODO use Exec::system($command);
        if ($workDir) {
            chdir($workDir);
        }

        // $hasOutput = false;
        $lastLine = system($command, $exitCode);

        $this->code   = $exitCode;
        $this->output = trim($lastLine);

        if ($exitCode !== 0) {
            $this->error = $this->output;
            // $hasOutput = true;
            Color::println("error code {$exitCode}:\n" . $lastLine, 'red');
        } else {
            echo "\n";
        }

        // if (false === $hasOutput &&$lastLine) {
        //     echo $lastLine . "\n";
        // }
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @return string
     */
    public function getWorkDir(): string
    {
        return $this->workDir;
    }

    /**
     * @param string $workDir
     *
     * @return CmdRunner
     */
    public function setWorkDir(string $workDir): self
    {
        $this->workDir = $workDir;
        return $this;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @return bool
     */
    public function isFail(): bool
    {
        return $this->code !== 0;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->code === 0;
    }

    /**
     * @param bool $trim
     *
     * @return string
     */
    public function getOutput(bool $trim = false): string
    {
        return $trim ? trim($this->output) : $this->output;
    }

    /**
     * @return array
     */
    public function getResult(): array
    {
        return [
            'code'   => $this->code,
            'output' => $this->output,
        ];
    }

    /**
     * @param bool $printCmd
     *
     * @return CmdRunner
     */
    public function setPrintCmd(bool $printCmd): self
    {
        $this->printCmd = $printCmd;
        return $this;
    }

    /**
     * @param bool $printOutput
     *
     * @return CmdRunner
     */
    public function setPrintOutput(bool $printOutput): self
    {
        $this->printOutput = $printOutput;
        return $this;
    }

    /**
     * @return array
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * @param array $commands
     *
     * @return CmdRunner
     */
    public function setCommands(array $commands): CmdRunner
    {
        $this->commands = $commands;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIgnoreError(): bool
    {
        return $this->ignoreError;
    }

    /**
     * @param bool $ignoreError
     *
     * @return CmdRunner
     */
    public function setIgnoreError(bool $ignoreError): self
    {
        $this->ignoreError = $ignoreError;
        return $this;
    }

    /**
     * @param bool $dryRun
     *
     * @return $this
     */
    public function setDryRun(bool $dryRun): self
    {
        $this->dryRun = $dryRun;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }
}
