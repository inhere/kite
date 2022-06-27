<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Attach\Gitlab;

use Inhere\Console\Command;
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Helper\AppHelper;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Str;
use function implode;
use function strpos;

/**
 * Class BranchDeleteCmd
 */
class BranchDeleteCmd extends Command
{
    protected static string $name = 'delete';
    protected static string $desc = 'quick delete branches from local, origin and main remote';

    public static function aliases(): array
    {
        return ['del'];
    }

    protected function configFlags(FlagsParser $fs): void
    {
        // $this->flags->addOptByRule($name, $rule);
    }

    /**
     * @options
     * -f, --force              bool;Force execute delete command, ignore error
     * --nm, --not-main         bool;Dont delete branch on the main remote
     *
     * @arguments
     *  branches...   array;The want deleted branch name(s). eg: fea_6_12;required
     *
     * @param Input  $input
     * @param Output $output
     *
     */
    protected function execute(Input $input, Output $output): void
    {
        $fs = $this->flags;
        $gl = AppHelper::newGitlab();

        $names = $fs->getArg('branches');
        if (!$names) {
            throw new PromptException('please input an branch name');
        }

        $force   = $fs->getOpt('force');
        $notMain = $fs->getOpt('not-main');
        $dryRun  = $this->flags->getOpt('dry-run');

        $deletedNum = 0;
        $mainRemote = $gl->getMainRemote();
        $output->colored('Will deleted: ' . implode(',', $names));
        foreach ($names as $name) {
            if (strpos($name, ',') > 0) {
                $nameList = Str::explode($name, ',');
            } else {
                $nameList = [$name];
            }

            foreach ($nameList as $brName) {
                $deletedNum++;
                $run = CmdRunner::new();
                $run->setDryRun($dryRun);

                if ($force) {
                    $run->setIgnoreError(true);
                }

                $this->doDeleteBranch($brName, $mainRemote, $run, $notMain);
            }
        }

        // $output->info('update git branch list after deleted');
        // git fetch main --prune
        // $run = CmdRunner::new();
        // $run->add('git fetch origin --prune');
        // $run->addf('git fetch %s --prune', $mainRemote);
        // $run->run(true);

        $output->success('Completed. Total delete: ' . $deletedNum);
    }

    /**
     * @param string $name
     * @param string $mainRemote
     * @param CmdRunner $run
     * @param bool $notMain
     */
    protected function doDeleteBranch(string $name, string $mainRemote, CmdRunner $run, bool $notMain): void
    {
        $this->output->title("delete the branch: $name", [
            'indent' => 0,
        ]);

        $run->addf('git branch --delete %s', $name);
        // git push origin --delete BRANCH
        $run->addf('git push origin --delete %s', $name);

        if (false === $notMain) {
            // git push main --delete BRANCH
            $run->addf('git push %s --delete %s', $mainRemote, $name);
        }

        $run->run(true);
    }

}
