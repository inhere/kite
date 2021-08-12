<?php declare(strict_types=1);

namespace Inhere\Kite\Common;

use Toolkit\Sys\Cmd\AbstractCmdBuilder;
use RuntimeException;
use Toolkit\Cli\Color;
use function is_array;
use function is_string;
use function sprintf;

/**
 * Class CmdRunner - batch run multi commands
 *
 * @package Inhere\Kite\Common
 */
class CmdRunner extends AbstractCmdBuilder
{
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
        parent::__construct('', $workDir);

        if (is_string($command)) {
            $this->command = $command;
        } elseif (is_array($command)) {
            $this->commands = (array)$command;
        }
    }

    /**
     * @param string $msg
     * @param string $scene
     */
    protected function printMessage(string $msg, string $scene): void
    {
        Cmd::printByScene($msg, $scene);
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
     * Run all added commands
     *
     * @param bool $printOutput
     *
     * @return $this
     */
    public function run(bool $printOutput = false): AbstractCmdBuilder
    {
        $this->printOutput = $printOutput;
        if ($command = $this->command) {
            $this->innerExecute($command, $this->workDir);

            // stop on error
            $code = $this->code;
            if (0 !== $code && false === $this->ignoreError) {
                Color::println("\nCommand exit code not equal to 0(code: $code), stop run.", 'red');
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
                    Color::println("Skip $step ...", 'cyan');
                    Color::println("- Does not meet the conditions", 'cyan');
                    continue;
                }

                $workDir = $item['workDir'] ?? $workDir;
                $command = $item['command'];
            }

            Color::println("STEP $step:", 'mga0');

            // custom work dir
            if ($workDir) {
                Color::println('- work dir is ' . $workDir, 'italic');
            }

            $this->innerExecute($command, $workDir);
            $step++;

            // stop on error
            $code = $this->code;
            if (0 !== $code && false === $this->ignoreError) {
                Color::println("\nCommand exit code not equal to 0(code: $code), stop run.", 'red');
                break;
            }
        }
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
}
