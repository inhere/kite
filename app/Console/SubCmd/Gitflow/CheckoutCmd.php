<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\Gitflow;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\GitLocal\GitFactory;
use PhpGit\Repo;
use Throwable;
use Toolkit\PFlag\FlagsParser;

/**
 * Class CheckoutCmd
 *
 * @package Inhere\Kite\Console\Controller\Gitlab
 */
class CheckoutCmd extends Command
{
    protected static string $name = 'checkout';
    protected static string $desc = 'quick delete git branches from local, origin, main remote';

    public static function aliases(): array
    {
        return ['co'];
    }

    protected function configFlags(FlagsParser $fs): void
    {
        // $this->flags->addOptByRule($name, $rule);
    }

    /**
     * checkout to another branch and update code to latest
     *
     * @arguments
     * branch       string;the target branch name;true
     *
     * @options
     *  -b, --branch        bool;create a new branch from current branch
     *  --np, --no-push     bool;dont push to origin remote after update
     *
     * @param Input $input
     * @param Output $output
     *
     * @return mixed
     * @throws Throwable
     */
    protected function execute(Input $input, Output $output): mixed
    {
        $fs = $this->flags;
        $br = $fs->getArg('branch');

        $dryRun = false;
        if ($p = $this->getParent()) {
            $dryRun = $p->getFlags()->getOpt('dry-run');
        }

        $repo  = Repo::new();
        $doNew = $fs->getOpt('branch');

        $output->info('update branches info by git fetch');
        $fetch = $repo->newCmd('fetch')->add('-n', '-p')->setDryRun($dryRun);
        $fetch->runAndPrint();

        $bs = $repo->getBranchInfos();
        if ($doNew) {

        }

        $needUp = true;
        if (!$bs->hasLocalBranch($br)) {
            $gx = GitFactory::make();

            if (!$bs->hasRemoteBranch($br, $gx->getForkRemote())) {

                if (!$doNew && !$bs->hasRemoteBranch($br, $gx->getMainRemote())) {
                    $output->warning("branch %s not exists in local and remotes, please create it.");
                    return 1;
                }

                $needUp = false;
            }
        }


        // run checkout
        $co = $repo->newCmd('checkout')
            ->setDryRun($dryRun)
            ->addIf('-b', $doNew)
            ->addArgs($br);

        $co->runAndPrint();
        if ($co->isFail()) {
            return 1;
        }

        if (!$doNew) {
            return 0;
        }

        if (!$needUp) {

            return 0;
        }

        $output->notice('update branch code to latest');

        $flags = [];
        if ($fs->getOpt('no-push')) {
            $flags[] = '--np';
        }

        $upCmd = new UpdatePushCmd($input, $output);
        $upCmd->run($flags);

        return 0;
    }
}
