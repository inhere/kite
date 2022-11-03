<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitxCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Kite;
use PhpGit\Git;
use Toolkit\Stdlib\Obj\DataObject;
use function implode;
use function sprintf;
use function strlen;
use function trim;

/**
 * class AddCommitPushCmd
 *
 * @author inhere
 * @date 2022/7/12
 */
class AddCommitPushCmd extends Command
{
    protected static string $name = 'acp';
    protected static string $desc = 'run git add/commit/push at once command';

    public static function aliases(): array
    {
        return ['ac-push'];
    }

    /**
     * run git add/commit/push at once command
     *
     * @options
     *  -m, --message             string;The commit message text
     *      --nm, --no-message    bool;not input message, write message by git interactive shell.
     *  --np, --not-push          bool;Dont execute git push
     *      --auto-sign           bool;Auto add sign string after message.
     *      --sign-text           Custom setting the sign text.
     *
     * @arguments
     *  files...   array;Only add special files
     *
     * @help
     * commit types:
     *  build     "Build system"
     *  chore     "Chore"
     *  ci        "CI"
     *  docs      "Documentation"
     *  feat      "Features"
     *  fix       "Bug fixes"
     *  perf      "Performance"
     *  refactor  "Refactor"
     *  style     "Style"
     *  test      "Testing"
     *
     * @param Input $input
     * @param Output $output
     *
     * @return void
     */
    protected function execute(Input $input, Output $output): void
    {
        $fs = $this->flags;

        $settings = DataObject::new(Kite::config()->getArray('git'));

        $message   = '';
        $noMessage = $fs->getOpt('no-message');
        if (!$noMessage) {
            $message = $fs->getOpt('message');
            if (!$message) {
                $output->liteError('please input an message for git commit');
                return;
            }

            $message = trim($message);
            if (strlen($message) < 3) {
                $output->liteError('the input commit message is too short');
                return;
            }
        }

        $added = '.';
        if ($args = $fs->getArg('files')) {
            $added = implode(' ', $args);
        }

        $signText = $fs->getOpt('sign-text', $settings->getString('sign-text'));
        $autoSign = $fs->getOpt('auto-sign', $settings->getBool('auto-sign'));

        // will auto fetch user info by git
        if ($autoSign && !$signText) {
            $git = Git::new();

            $username  = $git->config->get('user.name');
            $userEmail = $git->config->get('user.email');
            // eg "Signed-off-by: inhere <in.798@qq.com>"
            if ($username && $userEmail) {
                $signText = "$username <$userEmail>";
            }
        }

        $dryRun = false;
        if ($pfs = $this->getParentFlags()) {
            $dryRun = $pfs->getOpt('dry-run');
            // $yes = $pfs->getOpt('yes');
        }

        $run = CmdRunner::new("git status $added");
        $run->setDryRun($dryRun);

        $run->do(true);
        $run->afterOkDo("git add $added");
        if ($message) {
            if ($signText) {
                $message .= "\n\nSigned-off-by: $signText";
            }

            $run->afterOkDo(sprintf('git commit -m "%s"', $message));
        } else {
            $run->afterOkDo("git commit");
        }

        if (false === $fs->getOpt('not-push')) {
            $run->afterOkDo('git push');
        }

        $output->success('Complete');
    }
}
