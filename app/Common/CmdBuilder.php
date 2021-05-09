<?php declare(strict_types=1);

namespace Inhere\Kite\Common;

use Toolkit\Cli\Color;

/**
 * class CmdBuilder
 */
class CmdBuilder extends AbstractCmdBuilder
{
    /**
     * @var string
     */
    protected $bin = '';

    /**
     * @var array|string[]
     */
    protected $args = [];

    /**
     * @param string $bin
     * @param string $workDir
     *
     * @return static
     */
    public static function new(string $bin = '', string $workDir = ''): self
    {
        return new self($bin, $workDir);
    }

    /**
     * @param string $subCmd
     * @param string $gitBin
     *
     * @return static
     */
    public static function git(string $subCmd = '', string $gitBin = 'git'): self
    {
        $builder = new self($gitBin, '');

        if ($subCmd) {
            $builder->addArg($subCmd);
        }

        return $builder;
    }

    /**
     * CmdBuilder constructor.
     *
     * @param string $bin
     * @param string $workDir
     */
    public function __construct(string $bin = '', string $workDir = '')
    {
        parent::__construct('', $workDir);

        $this->setBin($bin);
    }

    /**
     * @param string|int $arg
     *
     * @return $this
     */
    public function add($arg): self
    {
        $this->args[] = $arg;
        return $this;
    }

    /**
     * @param string|int $arg
     * @param bool       $isOk
     *
     * @return $this
     */
    public function addIf($arg, bool $isOk): self
    {
        if ($isOk) {
            $this->args[] = $arg;
        }

        return $this;
    }

    /**
     * @param string|int $arg
     *
     * @return $this
     */
    public function addArg($arg): self
    {
        $this->args[] = $arg;
        return $this;
    }

    /**
     * @param ...$args
     *
     * @return $this
     */
    public function addArgs(...$args): self
    {
        $this->args = array_merge($this->args, $args);
        return $this;
    }

    /**
     * @param bool $printOutput
     *
     * @return AbstractCmdBuilder
     */
    public function run(bool $printOutput = false): AbstractCmdBuilder
    {
        $this->printOutput = $printOutput;

        $command = $this->buildCommandLine();
        $this->innerExecute($command, $this->workDir);

        // stop on error
        if (0 !== $this->code && false === $this->ignoreError) {
            Color::println("\nCommand exit code not equal to 0(code: {$this->code}), stop run.", 'red');
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function buildCommandLine(): string
    {
        $argList = [];
        foreach ($this->args as $arg) {
            // $argList[] = sprintf("'%s'", (string)$arg);
            $argList[] = (string)$arg;
        }

        $argString = implode(' ', $argList);

        return $this->bin . ' ' . $argString;
    }

    /**
     * @param string $bin
     *
     * @return CmdBuilder
     */
    public function setBin(string $bin): self
    {
        $this->bin = $bin;
        return $this;
    }

    /**
     * @param array|string[] $args
     *
     * @return CmdBuilder
     */
    public function setArgs(array $args): self
    {
        $this->args = $args;
        return $this;
    }
}
