<?php declare(strict_types=1);

namespace Inhere\Kite\Component;

use Toolkit\Stdlib\Obj\AbstractObj;
use function array_merge;

/**
 * class ScriptRunner
 */
class ScriptRunner extends AbstractObj
{
    /**
     * @var bool
     */
    private $dryRun = false;

    /**
     * @var bool
     */
    private $enable = true;

    /**
     * @var array
     */
    private $scripts = [];

    /**
     * @var array
     */
    private $scriptDirs = [];

    /**
     * @var array
     */
    private $scriptExts = ['.sh', '.bash', '.php'];

    /**
     * @var array
     */
    private $scriptFiles = [];

    /**
     * @var bool
     */
    private $autoScriptBin = true;

    /**
     * @var string[]
     */
    private $scriptExt2bin = [
        '.sh'   => 'sh',
        '.bash' => 'bash',
        '.php'  => 'php',
    ];

    /**
     * @return array
     */
    public function getScriptDirs(): array
    {
        return $this->scriptDirs;
    }

    /**
     * @param array $scriptDirs
     */
    public function setScriptDirs(array $scriptDirs): void
    {
        $this->scriptDirs = $scriptDirs;
    }

    /**
     * @return array
     */
    public function getScripts(): array
    {
        return $this->scripts;
    }

    /**
     * @param array $scripts
     */
    public function setScripts(array $scripts): void
    {
        $this->scripts = $scripts;
    }

    /**
     * @return bool
     */
    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    /**
     * @param bool $dryRun
     */
    public function setDryRun(bool $dryRun): void
    {
        $this->dryRun = $dryRun;
    }

    /**
     * @return bool
     */
    public function isEnable(): bool
    {
        return $this->enable;
    }

    /**
     * @param bool|int $enable
     */
    public function setEnable($enable): void
    {
        $this->enable = (bool)$enable;
    }

    /**
     * @return array
     */
    public function getScriptFiles(): array
    {
        return $this->scriptFiles;
    }

    /**
     * @return bool
     */
    public function isAutoScriptBin(): bool
    {
        return $this->autoScriptBin;
    }

    /**
     * @param bool|string $autoScriptBin
     */
    public function setAutoScriptBin($autoScriptBin): void
    {
        $this->autoScriptBin = (bool)$autoScriptBin;
    }

    /**
     * @return string[]
     */
    public function getScriptExt2bin(): array
    {
        return $this->scriptExt2bin;
    }

    /**
     * @param array<string> $scriptExt2bin
     */
    public function setScriptExt2bin(array $scriptExt2bin): void
    {
        $this->scriptExt2bin = array_merge($this->scriptExt2bin, $scriptExt2bin);
    }

    /**
     * @return array
     */
    public function getScriptExts(): array
    {
        return $this->scriptExts;
    }

    /**
     * @param array $scriptExts
     */
    public function setScriptExts(array $scriptExts): void
    {
        $this->scriptExts = $scriptExts;
    }
}
