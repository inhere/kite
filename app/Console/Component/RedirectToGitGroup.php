<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Component;

use Inhere\Console\Controller;
use Inhere\Kite\Console\Controller\Gitx\GitxController;
use Inhere\Kite\Kite;
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
    public array $cmdList = [];

    /**
     * @param Controller $ctrl
     * @param string $command
     * @param array $args
     *
     * @return bool
     * @throws Throwable
     */
    public function handle(Controller $ctrl, string $command, array $args): bool
    {
        if (!$this->cmdList) {
            return false;
        }

        $app = $ctrl->getApp();

        // resolve alias
        $gitCtrl = $app->getController(GitxController::getName());
        $action = $gitCtrl->resolveAlias($command);
        $group  = $ctrl->getRealGName();

        // if $first = *, will redirect all command.
        $first = $this->cmdList[0];
        if ($first === '*' || in_array($action, $this->cmdList, true)) {
            // auto proxy env
            Kite::autoProxy()->applyProxyEnv($action, $group);

            Cli::info("[NOTE] will redirect to git group and run subcommand: $action");
            $ctrl->debugf('group: %s - command "%s" not found, will redirect to the git group', $group, $command);

            $newArgs = [];
            if ($ctrl->getFlags()->getOpt('dry-run')) {
                $newArgs[] = '--dry-run';
            }
            if ($wd = $ctrl->getFlags()->getOpt('workdir')) {
                $newArgs[] = '--workdir';
                $newArgs[] = $wd;
            }

            $newArgs[] = $action;
            // append remaining args
            array_push($newArgs, ...$args);

            $app->dispatch('git', $newArgs);
            // $app->dispatch("git:$command", $args);
            return true;
        }

        return false;
    }
}
