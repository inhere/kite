<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Listener;

use Inhere\Console\ConsoleEvent;
use Inhere\Console\Handler\AbstractHandler;
use Inhere\Kite\Kite;
use Toolkit\Stdlib\Obj\AbstractObj;

/**
 * Class BeforeCommandRunListener
 *
 * @package Inhere\Kite\Console\Listener
 */
class BeforeCommandRunListener extends AbstractObj
{
    /**
     * @see ConsoleEvent::COMMAND_RUN_BEFORE
     */
    public function __invoke(AbstractHandler $handler)
    {
        // auto set proxy
        $realCName = $handler->getRealCName();
        $groupName = $handler->getRealGName();
        Kite::autoProxy()->applyProxyEnv($realCName, $groupName, $handler->getCommandId());
    }
}
