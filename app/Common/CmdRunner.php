<?php declare(strict_types=1);

namespace Inhere\Kite\Common;

use Inhere\Kite\Helper\SysCmd;
use Toolkit\Cli\Color;
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
    private $cmd;

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
    public static function new(string $cmd, string $workDir = ''): self
    {
        return new self($cmd, $workDir);
    }

    /**
     * Class constructor.
     *
     * @param string $cmd
     * @param string $workDir
     */
    public function __construct(string $cmd, string $workDir = '')
    {
        $this->cmd     = $cmd;
        $this->workDir = $workDir;
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
        if ($this->printCmd) {
            Color::println("> {$this->cmd}", 'yellow');
        }

        // $ret = SysCmd::exec($this->cmd, $this->workDir);
        $ret = SysCmd::exec2($this->cmd, $this->workDir);

        $this->code   = $ret['code'];
        $this->output = $ret['output'];

        // print output
        if ($this->printOutput = $printOutput) {
            echo $this->output;
        }

        return $this;
    }

    /**
     * @param string      $cmd
     * @param string|null $workDir
     *
     * @return $this
     */
    public function okDoRun(string $cmd, string $workDir = null): self
    {
        if (0 !== $this->code) {
            return $this;
        }

        if ($workDir !== null) {
            $this->workDir = $workDir;
        }

        $this->cmd = $cmd;

        return $this->do($this->printOutput);
    }

    /**
     * @return string
     */
    public function getCmd(): string
    {
        return $this->cmd;
    }

    /**
     * @param string $cmd
     *
     * @return CmdRunner
     */
    public function setCmd(string $cmd): self
    {
        $this->cmd = $cmd;
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
}
