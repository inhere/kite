<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Listener;

use Inhere\Console\AbstractHandler;
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
     * @var array
     */
    public $groupNames = [];

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
        $alone = $handler->isAlone();

        $this->autoSetProxyEnv($input, $alone, $handler::getName());
    }

    /**
     * @param Input  $input
     * @param bool   $alone
     * @param string $commandName
     */
    protected function autoSetProxyEnv(Input $input, bool $alone, string $commandName): void
    {
        if (!$this->envSettings) {
            return;
        }

        $cmdId = $input->getCommandId();
        if ($this->commandIds && in_array($cmdId, $this->commandIds, true)) {
            $this->setProxyEnv($this->envSettings, $cmdId);
            return;
        }

        if (!$alone && $this->groupNames && in_array($commandName, $this->groupNames, true)) {
            $this->setProxyEnv($this->envSettings, $cmdId);
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
