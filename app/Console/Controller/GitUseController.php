<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Controller;

use Inhere\Console\Controller;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Helper\GitUtil;
use function sprintf;

/**
 * Class GitUseController
 * - git:tag:push   add tag and push to remote
 * - git:tag:delete delete the tag on remote
 *
 */
class GitUseController extends Controller
{
    protected static $name = 'git';

    protected static $description = 'Some useful tool commands for quick use git';

    public static function aliases(): array
    {
        return ['g'];
    }

    protected static function commandAliases(): array
    {
        return [
            'tl'      => 'tagList',
            'cl'      => 'changelog',
            'tag:del' => 'tagDelete',
            'taglist' => 'tagList',
            'tagFind' => ['tagfind', 'tag-find', 'tag:find'],
            'tagPush' => [
                'tagpush',
                'tp',
                'tag-push',
                'tag:push'
            ],
            'tagList' => ['tag', 'tl', 'taglist']
        ];
    }

    public function statusCommand(): void
    {
        $commands = [
            'echo hi',
            'git status'
        ];

        CmdRunner::new()->batch($commands)->run();
    }

    /**
     * Clone an remote git repository to local
     *
     * @options
     *  --gh        Define the remote repository is on github
     *
     * @arguments
     *  repo    The remote git repo URL or repository name
     *  name    The repository name at local, default is same `repo`
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {fullCmd}  php-toolkit/cli-utils --gh
     *  {fullCmd}  php-toolkit/cli-utils my-repo --gh
     *  {fullCmd}  https://github.com/php-toolkit/cli-utils
     */
    public function cloneCommand(Input $input, Output $output): void
    {
        $output->success('TODO');
    }

    /**
     * get the latest/next git tag from the project directory
     *
     * @options
     * -d, --dir      The project directory path. default is current directory.
     * --next-tag     Display the project next tag version. eg: v2.0.2 => v2.0.3
     * --only-tag     Only output tag information
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *   {fullCmd}
     *   {fullCmd} --only-tag
     *   {fullCmd} -d ../view --next-tag
     *   {fullCmd} -d ../view --next-tag --only-tag
     *
     */
    public function tagFindCommand(Input $input, Output $output): void
    {
        $dir = $input->getOpt('dir', $input->getOpt('d'));
        $dir = $dir ?: $input->getPwd();

        $onlyTag = $input->getBoolOpt('only-tag');
        $nextTag = $input->getBoolOpt('next-tag');

        $tagName = GitUtil::findTag($dir, !$onlyTag);
        if (!$tagName) {
            $output->error('No any tags of the project');
            return;
        }

        $title = '<info>The latest tag version</info>: <b>%s</b>';

        if ($nextTag) {
            $title   = "<info>The next tag version</info>: <b>%s</b> (current: {$tagName})";
            $tagName = GitUtil::buildNextTag($tagName);
        }

        if ($onlyTag) {
            echo $tagName;
            return;
        }

        $output->printf($title, $tagName);
    }

    /**
     * list all git tags for the project
     *
     * @options
     *  --filter    Filter by input keywords
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {binWithCmd} --filter v2
     */
    public function tagListCommand(Input $input, Output $output): void
    {
        $cmd = 'git tag -l';

        $kw = $input->getStringOpt('filter');
        if ($kw) {
            $cmd .= " | grep $kw";
        }

        CmdRunner::new($cmd)->do(true);

        $output->success('Complete');
    }

    /**
     * Add new tag version and push to the remote git repos
     *
     * @options
     *  -v, --version       The new tag version. e.g: v2.0.4
     *  -m, --message       The message for add new tag.
     *      --dry-run       Dont real send git tag and push command
     *      --next          Auto calc next version for add new tag.
     *
     * @param Input  $input
     * @param Output $output
     */
    public function tagPushCommand(Input $input, Output $output): void
    {
        $lTag = '';
        $dir  = $input->getPwd();

        if ($input->getBoolOpt('next')) {
            $lTag = GitUtil::findTag($dir, false);
            if (!$lTag) {
                $output->error('No any tags found of the project');
                return;
            }

            $tag = GitUtil::buildNextTag($lTag);
        } else {
            $tag = $input->getSameStringOpt(['v', 'version']);
            if (!$tag) {
                $output->error('please input new tag version, like: -v v2.0.4');
                return;
            }
        }

        if (!AppHelper::isVersion($tag)) {
            $output->error('please input an valid tag version, like: -v v2.0.4');
            return;
        }

        $info = [
            'Work Dir' => $dir,
            'New Tag'  => $tag,
        ];

        if ($lTag) {
            $info['Old Tag'] = $lTag;
        }

        $msg = $input->getSameArg(['m', 'message']);
        $msg = $msg ?: "Release $tag";
        // add message
        $info['Message'] = $msg;

        $output->aList($info, 'Information', ['ucFirst' => false]);

        if ($this->isInteractive() && $output->unConfirm('please ensure create and push new tag')) {
            $output->colored('  GoodBye!');
            return;
        }

        // git tag -a $1 -m "Release $1"
        // git push origin --tags
        $cmd = sprintf('git tag -a %s -m "%s" && git push origin %s', $tag, $msg, $tag);

        $dryRun = $input->getBoolOpt('dry-run');
        if ($dryRun) {
            $output->info('... DRY-RUN ...');
            $output->colored('> ' . $cmd, 'ylw');
        } else {
            CmdRunner::new($cmd)->do(true);
        }

        $output->success('Complete');
    }

    /**
     * delete an remote tag by git
     *
     * @usage
     *  {command} [-S HOST:PORT]
     *  {command} [-H HOST] [-p PORT]
     *
     * @options
     *  -r, --remote The remote name. default <comment>origin</comment>
     *  -v, --tag    The tag version. eg: v2.0.3
     *
     * @param Input  $input
     * @param Output $output
     */
    public function tagDeleteCommand(Input $input, Output $output): void
    {
        $remote = $input->getSameOpt(['r', 'remote'], 'origin');
        $tag    = $input->getSameOpt(['v', 'tag']);

        GitUtil::delRemoteTag($remote, $tag);

        $output->success('Complete');
    }

    /**
     * run git add/commit at once command
     *
     * @options
     *  -m, --message The commit message
     *
     * @param Input  $input
     * @param Output $output
     */
    public function acCommand(Input $input, Output $output): void
    {
        $message = $input->getSameStringOpt(['m', 'message']);
        if (!$message) {
            $output->liteError('please input an message for git commit');
            return;
        }

        $output->info('Work Dir: ' . $input->getPwd());

        $run = CmdRunner::new('git add .')->do(true);
        $run->afterOkDo(sprintf('git commit -m "%s"', $message));

        $output->success('Complete');
    }

    /**
     * run git add/commit/push at once command
     *
     * @options
     *  -m, --message The commit message
     *
     * @param Input  $input
     * @param Output $output
     */
    public function acpCommand(Input $input, Output $output): void
    {
        $message = $input->getSameStringOpt(['m', 'message']);
        if (!$message) {
            $output->liteError('please input an message for git commit');
            return;
        }

        $output->info('Work Dir: ' . $input->getPwd());

        $run = CmdRunner::new('git add .')->do(true);
        $run->afterOkDo(sprintf('git commit -m "%s"', $message));
        $run->afterOkDo('git push');

        $output->success('Complete');
    }

    public function changelogCommand(): void
    {

    }
}
