<?php declare(strict_types=1);

namespace Inhere\Kite\Common;

use RuntimeException;
use Toolkit\Cli\Color;
use Toolkit\Sys\Sys;
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
    private $output = '';

    /**
     * [
     *  'echo hi',
     *  'do something'
     * ]
     *
     * @var array
     */
    private $commands = [];

    /**
     * Ignore check prevision return code
     *
     * @var bool
     */
    private $ignoreCode = false;

    /**
     * @var bool
     */
    private $printCmd = true;

    /**
     * @var bool
     */
    private $printOutput = false;

    /**
     * @param string $cmd
     * @param string $workDir
     *
     * @return static
     */
    public static function new(string $cmd = '', string $workDir = ''): self
    {
        return new self($cmd, $workDir);
    }

    /**
     * Class constructor.
     *
     * @param string $command
     * @param string $workDir
     */
    public function __construct(string $command = '', string $workDir = '')
    {
        $this->command = $command;
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

    /**
     * @param bool $printOutput
     *
     * @return array
     */
    public function exec(bool $printOutput = false): array
    {
        $this->do($printOutput);

        return $this->getResult();
    }

    /**
     * @param bool $printOutput
     *
     * @return $this
     */
    public function do(bool $printOutput = false): self
    {
        $this->printOutput = $printOutput;
        $this->execute($this->command);

        return $this;
    }

    /**
     * @param string $command
     */
    protected function execute(string $command): void
    {
        if (!$command) {
            throw new RuntimeException('The execute command cannot be empty');
        }

        if ($this->printCmd) {
            Color::println("> {$command}", 'yellow');
        }

        // $ret = SysCmd::exec($this->cmd, $this->workDir);
        $ret = Sys::execute($command, true, $this->workDir);

        $this->code   = $ret['status'];
        $this->output = $ret['output'];

        // print output
        if ($this->printOutput && $ret['output']) {
            echo $ret['output'] . "\n";
        }

    }

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

    public function run(): self
    {

    }

    /**
     * @param string $cmd
     *
     * @return $this
     */
    public function afterRun(string $cmd): self
    {
        return $this->setCommand($cmd)->do($this->printOutput);
    }

    /**
     * @param string   $cmd
     * @param callable $whereFunc
     *
     * @return $this
     */
    public function whereRun(string $cmd, callable $whereFunc) :self
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
    public function afterOkRun(string $cmd, string $workDir = null): self
    {
        if (0 !== $this->code) {
            return $this;
        }

        if ($workDir !== null) {
            $this->workDir = $workDir;
        }

        $this->command = $cmd;

        return $this->do($this->printOutput);
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
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
     * @param bool $ignoreCode
     *
     * @return self
     */
    public function setIgnoreCode(bool $ignoreCode): self
    {
        $this->ignoreCode = $ignoreCode;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIgnoreCode(): bool
    {
        return $this->ignoreCode;
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
