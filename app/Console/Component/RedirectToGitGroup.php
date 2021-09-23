<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Component;

use Inhere\Console\Application;
use Inhere\Console\Console;
use Inhere\Kite\Console\Controller\GitController;
use Throwable;
use Toolkit\Cli\Cli;
use Toolkit\Stdlib\Obj\AbstractObj;
use function in_array;

/**
 * class RedirectToGitGroup
 */
class RedirectToGitGroup extends AbstractObj
{
    /**
     * @var array
     */
    public $cmdList = [];

    /**
     * @param Application $app
     * @param string $action
     * @param array $args
     *
     * @return bool
     * @throws Throwable
     */
    public function handle(Application $app, string $action, array $args):bool
    {
        // resolve alias
        $gitCtrl = $app->getController(GitController::getName());
        $command = $gitCtrl->resolveAlias($action);

        $redirectList = $this->cmdList;
        if (in_array($command, $redirectList, true)) {
            Cli::info("NOTICE: will redirect to git group for run `git $command`");
            // Console::app()->dispatch("git:$command");
            // Console::app()->dispatch("git:$command", $this->flags->getRawArgs());
            Console::app()->dispatch("git:$command", $args);
            return true;
        }

        return false;
    }
}
