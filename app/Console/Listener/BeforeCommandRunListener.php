<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Listener;

use Inhere\Console\Handler\AbstractHandler;
use Inhere\Console\ConsoleEvent;
use Inhere\Console\IO\Input;
use Inhere\Console\Util\Show;
use Toolkit\Stdlib\Obj\AbstractObj;
use Toolkit\Stdlib\OS;
use function in_array;
use function vdump;

/**
 * Class BeforeCommandRunListener
 *
 * @package Inhere\Kite\Console\Listener
 */
class BeforeCommandRunListener extends AbstractObj
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
    public $envSettings = [];

    /**
     * ```ph
     *  groupName => [], // for the group
     *  // for special subcommands in the group
     *  groupName => ['sub1', 'sub2'],
     * ```
     *
     * @var array[]
     */
    public $groupLimits = [];

    /**
     * @var array
     */
    public $commandIds = [];

    /**
     * @see ConsoleEvent::COMMAND_RUN_BEFORE
     */
    public function __invoke(AbstractHandler $handler)
    {
        $input = $handler->getInput();
        $this->autoSetProxyEnv($input, $handler);
    }

    /**
     * @param Input $input
     * @param AbstractHandler $handler
     */
    protected function autoSetProxyEnv(Input $input, AbstractHandler $handler): void
    {
        if (!$this->envSettings) {
            return;
        }

        $alone = $handler->isAlone();
        $cmdId = $input->getCommandId();

        if ($this->commandIds && in_array($cmdId, $this->commandIds, true)) {
            $this->setProxyEnv($this->envSettings, $cmdId);
            return;
        }

        $groupName = $handler->getGroupName();
        if (!$alone && isset($this->groupLimits[$groupName])) {
            if (!$this->groupLimits[$groupName]) {
                $this->setProxyEnv($this->envSettings, $cmdId);
                return;
            }

            $subName = $input->getSubCommand();

            if (in_array($subName, $this->groupLimits[$groupName], true)) {
                $this->setProxyEnv($this->envSettings, $cmdId);
            }
        }
    }

    /**
     * @param array  $settings
     * @param string $command
     */
    protected function setProxyEnv(array $settings, string $command): void
    {
        Show::info('load and set proxy Env settings for commandID - ' . $command);
        Show::aList($settings, 'Set Proxy ENV From Config: "autoProxy"', [
            'ucFirst'      => false,
            'ucTitleWords' => false,
        ]);

        OS::setEnvVars($settings);
    }
}
