<?php declare(strict_types=1);

namespace Inhere\Kite\Component;

use Toolkit\Stdlib\Obj\AbstractObj;

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
}
