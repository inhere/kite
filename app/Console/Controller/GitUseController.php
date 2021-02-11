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
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Helper\GitUtil;
use PhpGit\Repo;
use function sprintf;
use function strtolower;
use function trim;

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
            'changelog' => ['clog', 'cl'],
            'tagDelete' => [
                'tag-del',
                'tagdel',
                'tag:del',
                'tag-rm',
                'tagrm',
                'tr',
                'rm-tag',
                'rmtag',
            ],
            'tagFind'   => ['tagfind', 'tag-find'],
            'tagNew'    => [
                'tagnew',
                'tag-new',
                'tn',
                'newtag',
                'new-tag',
                'tagpush',
                'tp',
                'tag-push',
            ],
            'tagList'   => ['tag', 'tags', 'tl', 'taglist'],
            'tagInfo'   => ['tag-info', 'ti', 'tag-show'],
        ];
    }

    /**
     * @param string $action
     *
     * @return bool
     */
    protected function onNotFound(string $action): bool
    {
        $this->output->info("input sub-command is '$action', will try exec system command `git $action`");

        $run = CmdRunner::new($this->input->getFullScript());
        $run->do(true);

        // return $run->isSuccess();
        return true;
    }

    public function statusCommand(Input $input, Output $output): void
    {
        $commands = [
            'echo hi',
            'git status'
        ];

        CmdRunner::new()->batch($commands)->run(true);
    }

    /**
     * display git information for the project
     *
     * @param Input  $input
     * @param Output $output
     */
    public function infoCommand(Input $input, Output $output): void
    {
        $repo = Repo::new();

        $output->aList($repo->getInfo(), 'Project Info', [
            'ucFirst' => false,
        ]);
    }

    /**
     * batch update multi dir by git pull
     */
    public function batchPullCommand(): void
    {
        $commands = [
            'git status',
            'git remote -v',
        ];

        CmdRunner::new()->batch($commands)->run(true);
    }

    /**
     * Open the git repository URL by browser
     *
     * @arguments
     *  remote    The remote name for open. If not input, will use `origin`
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {binWithCmd}
     *  {binWithCmd} other-remote
     */
    public function openCommand(Input $input, Output $output): void
    {
        $input->bindArgument('remote', 0);

        $remote = $input->getStringArg('remote', 'origin');

        $repo = Repo::new();
        $info = $repo->getRemoteInfo($remote);

        AppHelper::openBrowser($info->getHttpUrl());

        $output->success('Complete');
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
     *  {binWithCmd} php-toolkit/cli-utils --gh
     *  {binWithCmd} php-toolkit/cli-utils my-repo --gh
     *  {binWithCmd} https://github.com/php-toolkit/cli-utils
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
     * @arguments
     *  keywords    Filter by input keywords
     *
     * @param Input  $input
     * @param Output $output
     */
    public function tagListCommand(Input $input, Output $output): void
    {
        $cmd = 'git tag -l -n2';
        $kw  = $input->getStringArg(0);
        if ($kw) {
            $cmd .= " | grep $kw";
        }

        CmdRunner::new($cmd)->do(true);

        $output->success('Complete');
    }

    /**
     * display git tag information by `git show TAG`
     *
     * @arguments
     *  tag    Tag name for show info
     *
     * @param Input  $input
     * @param Output $output
     */
    public function tagInfoCommand(Input $input, Output $output): void
    {
        $tag = $input->getRequiredArg(0, 'tag');

        $commands = [
            "git show $tag",
        ];

        CmdRunner::new()->batch($commands)->run(true);
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
    public function tagNewCommand(Input $input, Output $output): void
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

        $dryRun = $input->getBoolOpt('dry-run');

        // git tag -a $1 -m "Release $1"
        // git push origin --tags
        // $cmd = sprintf('git tag -a %s -m "%s" && git push origin %s', $tag, $msg, $tag);
        $run = CmdRunner::new();
        $run->setDryRun($dryRun);
        $run->addf('git tag -a %s -m "%s"', $tag, $msg);
        $run->addf('git push origin %s', $tag);
        $run->run(true);

        $output->success('Complete');
    }

    /**
     * delete an local and remote tag by `git tag`
     *
     * @usage
     *  {command} [-S HOST:PORT]
     *  {command} [-H HOST] [-p PORT]
     *
     * @options
     *  -r, --remote        The remote name. default <comment>origin</comment>
     *  -v, --tag           The tag version. eg: v2.0.3
     *      --no-remote     Only delete local tag
     *
     * @param Input  $input
     * @param Output $output
     */
    public function tagDeleteCommand(Input $input, Output $output): void
    {
        $tag = $input->getSameOpt(['v', 'tag']);
        if (!$tag) {
            throw new PromptException('please input tag name');
        }

        $run = CmdRunner::new();
        $run->addf('git tag -d %s', $tag);

        if (false === $input->getBoolOpt('no-remote')) {
            $remote = $input->getSameStringOpt(['r', 'remote'], 'origin');

            $run->addf('git push %s :refs/tags/%s', $remote, $tag);
        }

        $run->run(true);

        $output->success('Complete');
    }

    /**
     * run git add/commit at once command
     *
     * @options
     *  -m, --message The commit message
     *
     * @arguments
     *  files...   Only add special files
     *
     * @param Input  $input
     * @param Output $output
     */
    public function acCommand(Input $input, Output $output): void
    {
        $input->setLOpt('not-push', true);

        $this->acpCommand($input, $output);
    }

    /**
     * run git add/commit/push at once command
     *
     * @options
     *  -m, --message   The commit message
     *      --not-push  Dont execute git push
     *      --dry-run   Dont real execute command
     *
     * @arguments
     *  files...   Only add special files
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

        $added = '.';
        if ($args = $input->getArguments()) {
            $added = implode(' ', $args);
        }

        $dryRun = $input->getBoolOpt('dry-run');

        $run = CmdRunner::new("git status $added");
        $run->setDryRun($dryRun);

        $run->do(true);
        $run->afterOkDo("git add $added");
        $run->afterOkDo(sprintf('git commit -m "%s"', $message));

        if (false === $input->getBoolOpt('not-push')) {
            $run->afterOkDo('git push');
        }


        $output->success('Complete');
    }

    protected function changelogConfigure(Input $input): void
    {
        $input->bindArguments([
            'oldVersion' => 0,
            'newVersion' => 1,
        ]);
    }

    /**
     * collect git change log information by `git log`
     *
     * @arguments
     *  oldVersion   The old version. eg: v1.0.2
     *                - keywords `latest` will auto use latest tag.
     *  newVersion   The new version. eg: v1.2.3
     *                - keywords `head` will use `Head` commit.
     *
     * @options
     *  --file        Export changelog message to file
     *  --max-commit  Max parse how many commits
     *  --format      The git log option `--pretty` value.
     *                 can be one of oneline, short, medium, full, fuller, reference, email, raw, format:<string> and tformat:<string>.
     *
     * @param Input  $input
     * @param Output $output
     */
    public function changelogCommand(Input $input, Output $output): void
    {
        // useful options:
        // --no-merges
        // --glob=<glob-pattern>
        // --exclude=<glob-pattern>
        // --pretty[=<format>] <format> can be one of oneline, short, medium, full, fuller, reference, email, raw, format:<string> and tformat:<string>.

        // git log v1.0.7...v1.0.8 --pretty=format:'<project>/commit/%H %s' --reverse

        // git log v1.0.7...v1.0.7 --pretty=format:'<li> <a href="http://github.com/inhere/<project>/commit/%H">view commit &bull;</a> %s</li> ' --reverse
        // git log v1.0.7...HEAD --pretty=format:'<li> <a href="http://github.com/inhere/<project>/commit/%H">view commit &bull;</a> %s</li> ' --reverse
        $oldVersion = $input->getStringArg('oldVersion');
        if ($oldVersion) {
            if ($oldVersion === 'latest') {
                $oldVersion = GitUtil::findTag();
                $output->info('auto find latest tag ' . $oldVersion);
            }

            $newVersion = $input->getRequiredArg('newVersion');
            if (strtolower($newVersion) === 'head') {
                $newVersion = 'HEAD';
            }

            $logCmd = <<<CMD
git log $oldVersion...$newVersion --pretty=format:'<project>/commit/%H %s' --reverse
CMD;

            $runner = CmdRunner::new(trim($logCmd));
            $runner->do(true);

            $output->success('Complete');
            return;
        }

        $maxCommit = $input->getIntOpt('max-commit', 15);

        // git log --color --graph --pretty=format:'%Cred%h%Creset:%C(ul yellow)%d%Creset %s (%Cgreen%cr%Creset, %C(bold blue)%an%Creset)' --abbrev-commit -10
        $logCmd = <<<CMD
git log --color --graph --pretty=format:'%Cred%h%Creset:%C(ul yellow)%d%Creset %s (%Cgreen%cr%Creset, %C(bold blue)%an%Creset)' --abbrev-commit -$maxCommit
CMD;

        $runner = CmdRunner::new(trim($logCmd));
        $runner->do(true);

        $output->success('Complete');
    }
}
