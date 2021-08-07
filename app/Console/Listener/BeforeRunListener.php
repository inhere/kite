<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Listener;

use Inhere\Console\ConsoleEvent;
use Inhere\Kite\Console\CliApplication;
use Inhere\Kite\Kite;

/**
 * Class BeforeRunListener
 *
 * @package Inhere\Kite\Console\Listener
 */
class BeforeRunListener
{
    /**
     * @param CliApplication $app
     * @see ConsoleEvent::ON_BEFORE_RUN
     */
    public function __invoke(CliApplication $app)
    {
        Kite::logger()->info('will run command: ' . $app->getInput()->getCommand(), [
            'flags' => $app->getInput()->getFlags(),
        ]);
    }
}
