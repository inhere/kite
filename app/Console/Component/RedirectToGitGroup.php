<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Component;

use Inhere\Console\Controller;
use Inhere\Kite\Console\Controller\GitController;
use Throwable;
use Toolkit\Cli\Cli;
use Toolkit\Stdlib\Obj\AbstractObj;
use function array_push;
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
     * @param Controller $ctrl
     * @param string $action
     * @param array $args
     *
     * @return bool
     * @throws Throwable
     */
    public function handle(Controller $ctrl, string $action, array $args): bool
    {
        $app = $ctrl->getApp();

        // resolve alias
        $gitCtrl = $app->getController(GitController::getName());
        $command = $gitCtrl->resolveAlias($action);

        if (in_array($command, $this->cmdList, true)) {
            Cli::info("NOTICE: will redirect to git group for run subcommand: $command");
            $ctrl->debugf('command %s not found on %s, will redirect to the git group', $command, $ctrl->getGroupName());

            $newArgs = [];
            if ($ctrl->getFlags()->getOpt('dry-run')) {
                $newArgs[] = '--dry-run';
            }

            $newArgs[] = $command;
            // append remaining args
            array_push($newArgs, ...$args);

            $app->dispatch('git', $newArgs);
            // $app->dispatch("git:$command", $args);
            return true;
        }

        return false;
    }
}
