<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Jump;

use Toolkit\Stdlib\Obj;
use Toolkit\Sys\Sys;

/**
 * Class QuickJump - quick jump directory
 *
 * @package Inhere\Kite\Lib
 */
class QuickJump
{
    private $initialized = false;

    /**
     * @var string
     */
    private $datafile = '';

    /**
     * @var JumpStorage
     */
    private $engine;

    /**
     * @var array
     */
    private $aliases = [];

    /**
     * @param array $config
     *
     * @return static
     */
    public static function new(array $config = []): self
    {
        return new self($config);
    }

    /**
     * Class constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        Obj::init($this, $config);
    }

    public function init(): void
    {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        if (!$this->datafile) {
            $this->datafile = Sys::useHomeDir() . '/.config/quick-jump.json';
        }

        $this->engine = new JumpStorage($this->datafile);
        $this->engine->init();
        $this->engine->loadNamedPaths($this->aliases);
        $this->engine->dump();
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function match(string $name): string
    {
        return $this->engine->matchOne($name);
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function matchAll(string $name): array
    {
        return $this->engine->matchAll($name);
    }

    /**
     * @param string $name
     * @param string $path
     * @param bool   $override
     *
     * @return bool
     */
    public function addNamed(string $name, string $path, bool $override = false): bool
    {
        return $this->engine->addNamed($name, $path, $override);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function addHistory(string $path): bool
    {
        return $this->engine->addHistory($path);
    }

    public function dump(): void
    {
        $this->engine->dump();
    }

    /**
     * @return string
     */
    public function getDatafile(): string
    {
        return $this->datafile;
    }

    /**
     * @param string $datafile
     */
    public function setDatafile(string $datafile): void
    {
        $this->datafile = $datafile;
    }

    /**
     * @return JumpStorage
     */
    public function getEngine(): JumpStorage
    {
        return $this->engine;
    }

    /**
     * @param array $aliases
     */
    public function setAliases(array $aliases): void
    {
        $this->aliases = $aliases;
    }
}
