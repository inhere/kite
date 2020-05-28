<?php declare(strict_types=1);

namespace Inhere\Kite\Common;

use Inhere\Kite\Helper\SysCmd;
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
     * @return array
     */
    public function exec(): array
    {
        $this->do();

        return $this->getResult();
    }

    /**
     * @return $this
     */
    public function do(): self
    {
        $ret = SysCmd::exec($this->cmd, $this->workDir);

        $this->code   = $ret['code'];
        $this->output = $ret['output'];

        if ($this->printOutput) {
            echo $this->output;
        }

        return $this;
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
    public function setCmd(string $cmd): CmdRunner
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
    public function setWorkDir(string $workDir): CmdRunner
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
     * @param bool $printOutput
     *
     * @return CmdRunner
     */
    public function setPrintOutput(bool $printOutput): CmdRunner
    {
        $this->printOutput = $printOutput;
        return $this;
    }
}
