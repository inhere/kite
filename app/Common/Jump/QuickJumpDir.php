<?php declare(strict_types=1);

namespace Inhere\Kite\Common\Jump;

use Toolkit\FsUtil\Dir;
use Toolkit\Stdlib\Json;
use Toolkit\Stdlib\Obj;
use Toolkit\Sys\Sys;
use function dirname;
use function file_exists;
use function file_get_contents;

/**
 * Class QuickJumpDir
 *
 * @package Inhere\Kite\Common
 */
class QuickJumpDir
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

        $this->engine = new JumpStorage;

        // ensure dir is created.
        Dir::mkdir(dirname($this->datafile));
    }

    public function run(): void
    {
        $this->init();

        $this->engine->loadFile($this->datafile, true);
        $this->engine->loadData($this->aliases);

        $this->dump();
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function match(string $name): string
    {
        $dir = $this->engine->matchOne($name);
        $this->engine->dumpTo($this->datafile);

        return $dir;
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

    public function dump(): void
    {
        $this->engine->dumpTo($this->datafile);
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
