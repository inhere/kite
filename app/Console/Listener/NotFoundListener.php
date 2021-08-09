<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Listener;

use Inhere\Console\ConsoleEvent;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Console\CliApplication;
use Inhere\Kite\Kite;
use Toolkit\Sys\Sys;

/**
 * Class NotFoundListener
 *
 * @package Inhere\Kite\Console\Listener
 */
final class NotFoundListener
{
    /**
     * @param string         $cmd
     * @param CliApplication $app
     *
     * @return bool
     * @see ConsoleEvent::ON_NOT_FOUND
     */
    public function __invoke(string $cmd, CliApplication $app): bool
    {
        $aliases = $app->getParam('aliases', []);

        // - is an command alias.
        if ($aliases && isset($aliases[$cmd])) {
            $realCmd = $aliases[$cmd];

            $app->notice("input command is alias name, will redirect to the real command '$realCmd'");
            $app->dispatch($realCmd);
            return true;
        }

        // check custom scripts
        $scripts = $app->getParam('scripts', []);
        if (!$scripts || !isset($scripts[$cmd])) {
            // - run plugin
            if (Kite::plugManager()->isPlugin($cmd)) {
                $app->notice("input is an plugin name, will run plugin: $cmd");
                Kite::plugManager()->run($cmd, $app);
                return true;
            }

            // - call system command.
            $this->callSystemCmd($cmd, $app);
            return true;
        }

        // - run custom scripts.
        $this->runCustomScript($cmd, $app);
        return true;
    }

    /**
     * @param string         $cmd
     * @param CliApplication $app
     */
    private function callSystemCmd(string $cmd, CliApplication $app): void
    {
        if ($cmd[0] === '\\') {
            $cmd = substr($cmd, 1);
        }

        if (!Sys::isExecutable($cmd)) {
            $app->error("the command '$cmd' is not exists in the kite or system");
            return;
        }

        $cmdLine = $app->getInput()->getFullScript();
        $app->notice("input command is not found, will call system command: $cmdLine");

        // call system command
        CmdRunner::new($cmdLine)->do(true);
    }

    /**
     * @param string         $cmd
     * @param CliApplication $app
     */
    private function runCustomScript(string $cmd, CliApplication $app): void
    {
        /** @see \Inhere\Kite\Console\Command\RunCommand::execute() */
        $app->note("command not found, redirect to run script: $cmd");

        $args = $app->getInput()->getArgs();
        $args = array_merge([$cmd], $args);

        $app->getInput()->setArgs($args, true);
        $app->dispatch('run');
    }
}
