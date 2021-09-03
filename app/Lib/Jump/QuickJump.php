<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Jump;

use Toolkit\Stdlib\Obj;
use Toolkit\Sys\Sys;
use function array_merge;
use function file_exists;
use function file_get_contents;
use const BASE_PATH;

/**
 * Class QuickJump - quick jump directory
 *
 * @package Inhere\Kite\Lib
 */
class QuickJump
{
    /**
     * @var bool
     */
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
     * Paths aliases
     *
     * @var array
     */
    private $aliases = [];

    /**
     * @var array {bash:string, zsh:string}
     */
    private $shellTplFiles = [
        JumpShell::NAME_BASH => BASE_PATH . '/resource/templates/quick-jump/jump.bash',
        JumpShell::NAME_ZSH  => BASE_PATH . '/resource/templates/quick-jump/jump.zsh',
    ];

    /**
     * @param array $config
     *
     * @return self
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
            $this->datafile = Sys::userHomeDir() . '/.config/kite-jump.json';
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
     * @param int    $flag
     *
     * @return array
     */
    public function matchAll(string $name, int $flag = JumpStorage::MATCH_BOTH): array
    {
        return $this->engine->matchAll($name, $flag);
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
     * @param string $shell
     *
     * @return string
     */
    public function getShellTplContents(string $shell): string
    {
        JumpShell::checkShellName($shell);

        $tplFile = $this->shellTplFiles[$shell];

        // not template file
        if (!$tplFile || !file_exists($tplFile)) {
            return JumpShell::getShellScript($shell);
        }

        return (string)file_get_contents($tplFile);
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

    /**
     * @param string $name
     * @param string $tplFile
     */
    public function setShellTplFile(string $name, string $tplFile): void
    {
        if (JumpShell::isSupported($name)) {
            $this->shellTplFiles[$name] = $tplFile;
        }
    }

    /**
     * @return array{bash:string, zsh:string}
     */
    public function getShellTplFiles(): array
    {
        return $this->shellTplFiles;
    }

    /**
     * @param array $shellTplFiles
     */
    public function setShellTplFiles(array $shellTplFiles): void
    {
        $this->shellTplFiles = array_merge($shellTplFiles, $shellTplFiles);
    }
}
