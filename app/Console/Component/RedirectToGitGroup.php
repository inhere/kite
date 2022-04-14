<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Component;

use Inhere\Console\Controller;
use Inhere\Kite\Console\Controller\Gitx\GitController;
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
     * @param string $action
     * @param array $args
     *
     * @return bool
     * @throws Throwable
     */
    public function handle(Controller $ctrl, string $action, array $args): bool
    {
        if (!$this->cmdList) {
            return false;
        }

        $app = $ctrl->getApp();

        // resolve alias
        $gitCtrl = $app->getController(GitController::getName());

        $action = $gitCtrl->resolveAlias($action);
        $group  = $ctrl->getRealGName();

        // if $first = *, will redirect all command.
        $first = $this->cmdList[0];
        if ($first === '*' || in_array($action, $this->cmdList, true)) {
            // auto proxy env
            Kite::autoProxy()->applyProxyEnv($action, $group);

            Cli::info("NOTICE: will redirect to git group for run subcommand: $action");
            $ctrl->debugf('command %s not found on %s, will redirect to the git group', $action, $group);

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
