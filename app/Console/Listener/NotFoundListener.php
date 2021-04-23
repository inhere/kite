<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Listener;

use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Console\Application;

/**
 * Class NotFoundListener
 *
 * @package Inhere\Kite\Console\Listener
 */
final class NotFoundListener
{
    /**
     * @param string      $cmd
     * @param Application $app
     *
     * @return bool
     */
    public function __invoke(string $cmd, Application $app): bool
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
            if ($app->getPlugManager()->isPlugin($cmd)) {
                $app->notice("input is an plugin name, will run plugin: $cmd");
                $app->getPlugManager()->run($cmd, $app);
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
     * @param string      $cmd
     * @param Application $app
     */
    private function callSystemCmd(string $cmd, Application $app): void
    {
        if ($cmd[0] === '\\') {
            $cmd = substr($cmd, 1);
        }

        $cmdLine = $app->getInput()->getFullScript();
        $app->notice("input command is not found, will call system command: $cmdLine");

        // call system command
        CmdRunner::new($cmdLine)->do(true);
    }

    /**
     * @param string      $cmd
     * @param Application $app
     */
    private function runCustomScript(string $cmd, Application $app): void
    {
        /** @see \Inhere\Kite\Console\Command\RunCommand::execute() */
        $app->note("command not found, redirect to run script: $cmd");

        $args = $app->getInput()->getArgs();
        $args = array_merge([$cmd], $args);

        $app->getInput()->setArgs($args, true);
        $app->dispatch('run');
    }
}
