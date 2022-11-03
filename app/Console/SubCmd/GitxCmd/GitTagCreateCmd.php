<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitxCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Helper\GitUtil;

/**
 * class GitTagDelCmd
 *
 * @author inhere
 * @date 2022/7/12
 */
class GitTagCreateCmd extends Command
{
    protected static string $name = 'create';
    protected static string $desc = 'create new tag version and push to the remote git repos';

    public static function aliases(): array
    {
        return ['new', 'make'];
    }

    /**
     * Add new tag version and push to the remote git repos
     *
     * @options
     *  -v, --version           The new tag version. e.g: v2.0.4
     *  -m, --message           The message for add new tag.
     *  --hash                  The hash ID for add new tag. default is HEAD
     *  -n, --next              bool;Auto calc next version for add new tag.
     *  --no-auto-add-v         bool;Not auto add 'v' for add tag version.
     *
     * @param Input $input
     * @param Output $output
     *
     * @return void
     */
    protected function execute(Input $input, Output $output): void
    {
        $fs = $this->flags;

        $lTag = '';
        $dir  = $input->getPwd();

        if ($fs->getOpt('next')) {
            $lTag = GitUtil::findTag($dir, false);
            if (!$lTag) {
                $output->error('No any tags found of the project');
                return;
            }

            $tag = GitUtil::buildNextTag($lTag);
        } else {
            $tag = $fs->getOpt('version');
            if (!$tag) {
                $output->error('please input new tag version, like: -v v2.0.4');
                return;
            }
        }

        if (!AppHelper::isVersion($tag)) {
            $output->error('please input an valid tag version, like: -v v2.0.4');
            return;
        }

        $dryRun = $yes = false;
        $hashId = $fs->getOpt('hash');

        if ($pfs = $this->getParentFlags()) {
            $dryRun = $pfs->getOpt('dry-run');
            $yes    = $pfs->getOpt('yes');
        }

        // $remotes = Git::new($dir)->remote->getList();
        if ($tag[0] !== 'v' && !$fs->getOpt('no-auto-add-v')) {
            $tag = 'v' . $tag;
        }

        $info = [
            'Work Dir' => $dir,
            'Hash ID'  => $hashId,
            'Dry Run'  => $dryRun,
            'New Tag'  => $tag,
        ];

        if ($lTag) {
            $info['Old Tag'] = $lTag;
        }

        $msg = $fs->getOpt('message');
        $msg = $msg ?: "Release $tag";

        // add message
        $info['Message'] = $msg;
        $output->aList($info, 'Information', ['ucFirst' => false]);

        $askMsg = 'please ensure create and push new tag';
        if (!$yes && $this->isInteractive() && $output->unConfirm($askMsg)) {
            $output->colored('  GoodBye!');
            return;
        }

        // git tag -a $1 -m "Release $1"
        // git push origin --tags
        // $cmd = sprintf('git tag -a %s -m "%s" && git push origin %s', $tag, $msg, $tag);
        $run = CmdRunner::new();
        $run->setDryRun($dryRun);
        $run->addf('git tag -a %s -m "%s" %s', $tag, $msg, $hashId);
        $run->addf('git push origin %s', $tag);
        $run->runAndPrint();

        $output->success('Complete');
    }
}
