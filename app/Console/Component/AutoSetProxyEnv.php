<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Component;

use Inhere\Console\Util\Show;
use Inhere\Kite\Kite;
use Toolkit\Stdlib\Obj\AbstractObj;
use Toolkit\Stdlib\OS;
use function in_array;

/**
 * class AutoSetProxyEnv
 */
class AutoSetProxyEnv extends AbstractObj
{
    /**
     * proxy env settings
     *
     * ```php
     * [
     *  // export http_proxy=http://127.0.0.1:1081; export https_proxy=http://127.0.0.1:1081;
     *  'http_proxy'  => 'http://127.0.0.1:1081',
     *  'https_proxy' => 'http://127.0.0.1:1081',
     * ]
     * ```
     *
     * @var array
     */
    public array $envSettings = [];

    /**
     * ```ph
     *  groupName => [], // for the group
     *  // for special subcommands in the group
     *  groupName => ['sub1', 'sub2'],
     * ```
     *
     * @var array[]
     */
    public array $groupLimits = [];

    /**
     * @var array
     */
    public array $commandIds = [];

    /**
     * @var string
     */
    private string $applyed = '';

    /**
     * @param string $realCName
     * @param string $realGName
     * @param string $cmdId
     *
     * @return bool
     */
    public function applyProxyEnv(string $realCName, string $realGName = '', string $cmdId = ''): bool
    {
        if (!$this->envSettings) {
            return false;
        }

        if (!$cmdId) {
            $cmdId = $realGName ? $realGName . ':' . $realCName : $realCName;
        }

        if ($cmdId === $this->applyed) {
            return true;
        }

        Kite::cliApp()->debugf('check can use proxy for commandID: %s', $cmdId);
        if ($this->commandIds && in_array($cmdId, $this->commandIds, true)) {
            $this->setProxyEnv($this->envSettings, $cmdId);
            return true;
        }

        if ($realGName && isset($this->groupLimits[$realGName])) {
            // for all subcommands
            if (!$this->groupLimits[$realGName]) {
                $this->setProxyEnv($this->envSettings, $cmdId);
                return true;
            }

            if (in_array($realCName, $this->groupLimits[$realGName], true)) {
                $this->setProxyEnv($this->envSettings, $cmdId);
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $cmdId
     *
     * @return bool
     */
    public function directApply(string $cmdId): bool
    {
        if (!$this->envSettings) {
            return false;
        }

        $this->setProxyEnv($this->envSettings, $cmdId);
        return true;
    }

    /**
     * @param array  $settings
     * @param string $command
     */
    protected function setProxyEnv(array $settings, string $command): void
    {
        $this->applyed = $command;

        Show::info('load and set proxy Env settings for commandID - ' . $command);
        Show::aList($settings, 'Set Proxy ENV From Config: "autoProxy"', [
            'ucFirst'      => false,
            'ucTitleWords' => false,
        ]);

        OS::setEnvVars($settings);
    }
}
