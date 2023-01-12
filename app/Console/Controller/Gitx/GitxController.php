<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Controller\Gitx;

use Inhere\Console\Controller;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\Cmd;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Common\GitLocal\GitHub;
use Inhere\Kite\Console\Manager\GitBranchManager;
use Inhere\Kite\Console\SubCmd\GitxCmd\AddCommitCmd;
use Inhere\Kite\Console\SubCmd\GitxCmd\AddCommitPushCmd;
use Inhere\Kite\Console\SubCmd\GitxCmd\BranchCmd;
use Inhere\Kite\Console\SubCmd\GitxCmd\ChangelogCmd;
use Inhere\Kite\Console\SubCmd\GitxCmd\GitEmojiCmd;
use Inhere\Kite\Console\SubCmd\GitxCmd\GitLogCmd;
use Inhere\Kite\Console\SubCmd\GitxCmd\GitTagCmd;
use Inhere\Kite\Console\SubCmd\GitxCmd\GitTagCreateCmd;
use Inhere\Kite\Console\SubCmd\GitxCmd\GitTagDelCmd;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Helper\GitUtil;
use Inhere\Kite\Kite;
use PhpGit\Repo;
use Throwable;
use Toolkit\Cli\Cli;
use Toolkit\Cli\Util\Clog;
use Toolkit\FsUtil\FS;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Helper\Assert;
use Toolkit\Stdlib\Obj\DataObject;
use Toolkit\Sys\Proc\ProcTasks;
use function realpath;

/**
 * Class GitxController
 */
class GitxController extends Controller
{
    protected static string $name = 'git';

    protected static string $desc = 'Provide useful tool commands for quick use git';

    /**
     * @var DataObject
     */
    private DataObject $settings;

    public static function aliases(): array
    {
        return ['g'];
    }

    protected static function commandAliases(): array
    {
        return [
                'tagDelete'    => [
                    'tag-del',
                    'tagdel',
                    'tag:del',
                    'tag-rm',
                    'tagrm',
                    'tr',
                    'rm-tag',
                    'rmtag',
                ],
                'branchUpdate' => ['brup', 'br-up', 'br-update', 'branch-up'],
                'update'       => ['up', 'pul', 'pull'],
                'batchPull'    => ['bp', 'bpul', 'bpull'],
                'tagFind'      => ['tagfind', 'tag-find'],
                'tagNew'       => [
                    'tagnew',
                    'tag-new',
                    'tn',
                    'newtag',
                    'new-tag',
                    'tagpush',
                    'tp',
                    'tag-push',
                ],
                'tagInfo'      => ['tag-info', 'ti', 'tag-show'],
            ] + [
                BranchCmd::getName()        => BranchCmd::aliases(),
                GitTagCmd::getName()        => GitTagCmd::aliases(),
                GitLogCmd::getName()        => GitLogCmd::aliases(),
                GitEmojiCmd::getName()        => GitEmojiCmd::aliases(),
                ChangelogCmd::getName()        => ChangelogCmd::aliases(),
                AddCommitCmd::getName()        => AddCommitCmd::aliases(),
                AddCommitPushCmd::getName() => AddCommitPushCmd::aliases(),
            ];
    }

    /**
     * @return array
     */
    protected function subCommands(): array
    {
        return [
            BranchCmd::class,
            GitTagCmd::class,
            GitLogCmd::class,
            GitEmojiCmd::class,
            AddCommitCmd::class,
            AddCommitPushCmd::class,
            ChangelogCmd::class,
        ];
    }

    /**
     * @return string[]
     */
    protected function getOptions(): array
    {
        return [
            '--try,--dry-run' => 'bool;Dry-run the workflow, dont real execute',
            '-y, --yes'       => 'bool;Direct execution without confirmation',
            '-w, --workdir'   => 'The command work dir, default is current dir.',
            // '-i, --interactive' => 'Run in an interactive environment[TODO]',
        ];
    }

    protected function beforeRun(): void
    {
        if ($this->app && !isset($this->settings)) {
            $this->settings = DataObject::new(Kite::config()->getArray('git'));
        }

        if ($workdir = $this->flags->getOpt('workdir')) {
            $workdir = realpath($workdir);
            $this->output->info('Change workdir to: ' . $workdir);
            $this->input->chWorkDir($workdir);
        } else {
            $workdir = $this->input->getWorkDir();
        }

        $this->output->info("Current workdir: $workdir");
    }

    /**
     * @param string $command
     * @param array $args
     *
     * @return bool
     */
    protected function onNotFound(string $command, array $args): bool
    {
        $this->output->info("input command '$command' is not found, will exec git command: `git $command`");

        $c = Cmd::git($command);
        $c->withIf(fn() => $c->addArgs(...$args), $args);
        $c->runAndPrint();

        return true;
    }

    /**
     * update codes from origin by git pull
     *
     * @arguments
     *  gitArgs  Input more args or opts for run git
     *
     * @param FlagsParser $fs
     * @param Input $input
     * @param Output $output
     *
     * @example
     *   {binWithCmd} --all -f --unshallow
     *   {binWithCmd} --all -f --unshallow
     *   {binWithCmd} --dir /path/to/mydir -- --all -f --unshallow
     */
    public function updateCommand(FlagsParser $fs, Input $input, Output $output): void
    {
        $args = $fs->getRawArgs();
        $dir  = $this->getFlags()->getOpt('workdir', $this->getWorkDir());
        Assert::isDir('.git', "$dir is not a git dir");

        $c = Cmd::git('pull');
        // $c->setWorkDir($dir);
        $c->setDryRun($this->flags->getOpt('dry-run'));
        $c->addArgs(...$args);
        $c->run(true);

        $output->success('Complete');
    }

    /**
     * push codes to origin by `git push`
     *
     * @arguments
     *  gitArgs  Input more args or opts for run git
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @example
     * {binWithCmd} -- -u origin main  # with custom args for call git push
     */
    public function pushCommand(FlagsParser $fs, Output $output): void
    {
        $args = $fs->getRawArgs();
        $dir  = $this->getFlags()->getOpt('workdir', $this->getWorkDir());
        Assert::isDir('.git', "$dir is not a git dir");

        $c = Cmd::git('push')
            // ->setWorkDir($dir)
            ->setDryRun($this->flags->getOpt('dry-run'))
            ->addArgs(...$args);
        $c->run(true);

        $output->success('Complete');
    }

    /**
     * @param Output $output
     */
    public function statusCommand(Output $output): void
    {
        $commands = [
            'git log -2',
            'git status' // git status -s
        ];

        CmdRunner::new()->batch($commands)->runAndPrint();

        $output->success('Complete');
    }

    /**
     * display git information for the project
     *
     * @options
     * --show-commands      bool;Show exec git commands
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function infoCommand(FlagsParser $fs, Output $output): void
    {
        // $dir  = $this->getFlags()->getOpt('workdir');
        $repo = Repo::new();
        $repo->setPrintCmd($fs->getOpt('show-commands'));

        $output->aList($repo->getInfo(), 'Project Info', [
            'ucFirst' => false,
        ]);
    }

    /**
     * Update branch list from remotes
     *
     * @arguments
     *  remote    The remote name for fetch. If not input, will use `origin`
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @example
     *  {binWithCmd}
     *  {binWithCmd} other-remote
     */
    public function branchUpdateCommand(FlagsParser $fs, Output $output): void
    {
        $remote = $fs->getArg('remote', 'origin');

        $gbm = new GitBranchManager();
        $gbm->update([$remote]);

        $output->success('Complete');
    }

    /**
     * batch update multi dir by git pull
     *
     * @options
     * --bd, --base, --base-dir     The base dir for all updated dirs. default is workDir
     *
     * @arguments
     * dirs     array;The want updated git repo dirs;true
     */
    public function batchPullCommand(FlagsParser $fs, Output $output): void
    {
        $baseDir = $fs->getOpt('base-dir') ?: $this->getWorkdir();

        $mpt = ProcTasks::new();
        foreach ($fs->getArg('dirs') as $dir) {
            $mpt->addTask(FS::join($baseDir, $dir));
        }

        $mpt->setTaskHandler(function (string $dir) {
            Cli::info('Git repo:', $dir);
            Cmd::git('pull')->setWorkDir($dir)->runAndPrint();
        })
            ->onCompleted(fn() => $output->success('Completed'))
            ->run();
    }

    /**
     * Open the git repository URL by browser
     *
     * @arguments
     *  remote    The remote name for open. If not input, will use `origin`
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @example
     *  {binWithCmd}
     *  {binWithCmd} other-remote
     */
    public function openCommand(FlagsParser $fs, Output $output): void
    {
        $remote = $fs->getArg('remote', 'origin');

        $repo = Repo::new();
        $info = $repo->getRemoteInfo($remote);

        AppHelper::openBrowser($info->getHttpUrl());

        $output->success('Complete');
    }

    /**
     * Clone an remote git repository to local
     *
     * @options
     *  --gh             bool;Define the remote repository is on github
     *
     * @arguments
     *  repo    string;The remote git repo URL or repository name;required
     *  name    The repository name at local, default is same `repo`
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @example
     *  {binWithCmd} php-toolkit/cli-utils --gh
     *  {binWithCmd} php-toolkit/cli-utils my-repo --gh
     *  {binWithCmd} https://github.com/php-toolkit/cli-utils
     */
    public function cloneCommand(FlagsParser $fs, Output $output): void
    {
        $repo = $fs->getArg('repo');
        $name = $fs->getArg('name');

        $c = Cmd::git('clone')
            // ->setWorkDir($this->flags->getOpt('workdir')) // fix: 前已经用 chdir 更改当前目录了
            ->setDryRun($this->flags->getOpt('dry-run'));

        if ($fs->getOpt('gh')) {
            $gh = GitHub::new($output);

            $repoUrl = $gh->parseRepoUrl($repo);
            if (!$repoUrl) {
                $output->error("invalid github 'repo' address: $repo");
                return;
            }
        } else {
            $repoUrl = $repo;
        }

        $args = $fs->getRemainArgs();
        $c->add($repoUrl)
            ->addIf($name, $name)
            ->withIf(fn() => $c->addArgs(...$args), $args);

        $c->runAndPrint();

        Clog::info('Complete');
    }

    /**
     * get the latest/next git tag from the project directory
     *
     * @options
     * -d, --dir          The project directory path. default is current directory.
     *     --next-tag     bool;Display the project next tag version. eg: v2.0.2 => v2.0.3
     *     --only-tag     bool;Only output tag information
     *
     * @param FlagsParser $fs
     * @param Input $input
     * @param Output $output
     *
     * @example
     *   {fullCmd}
     *   {fullCmd} --only-tag
     *   {fullCmd} -d ../view --next-tag
     *   {fullCmd} -d ../view --next-tag --only-tag
     *
     */
    public function tagFindCommand(FlagsParser $fs, Input $input, Output $output): void
    {
        $dir = $fs->getOpt('dir');
        $dir = $dir ?: $input->getPwd();

        $onlyTag = $fs->getOpt('only-tag');
        $nextTag = $fs->getOpt('next-tag');

        $tagName = GitUtil::findTag($dir, !$onlyTag);
        if (!$tagName) {
            $output->error('No any tags of the project');
            return;
        }

        $title = '<info>The latest tag version</info>: <b>%s</b>';

        if ($nextTag) {
            $title   = "<info>The next tag version</info>: <b>%s</b> (current: $tagName)";
            $tagName = GitUtil::buildNextTag($tagName);
        }

        if ($onlyTag) {
            echo $tagName;
            return;
        }

        $output->printf($title, $tagName);
    }

    /**
     * display git tag information by `git show TAG`
     *
     * @arguments
     *  tag     string;Tag name for show info;required
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function tagInfoCommand(FlagsParser $fs, Output $output): void
    {
        $tag = $fs->getArg('tag');

        $commands = [
            "git show $tag",
        ];

        CmdRunner::new()->batch($commands)->runAndPrint();
        $output->success('Complete');
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
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @throws Throwable
     * @deprecated
     */
    public function tagNewCommand(FlagsParser $fs, Output $output): void
    {
        $output->warning('TIP: deprecated, please call `git tag create`');
        $cmd = new GitTagCreateCmd($this->input, $output);
        $cmd->run($fs->getFlags());
    }

    /**
     * delete an local and remote tag by `git tag`
     *
     * @options
     *  -r, --remote        The remote name. default <comment>origin</comment>
     *  -v, --tag           The tag version. eg: v2.0.3
     *      --no-remote     bool;Only delete local tag
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @throws Throwable
     * @deprecated
     */
    public function tagDeleteCommand(FlagsParser $fs, Output $output): void
    {
        $output->warning('TIP: deprecated, please call `git tag delete`');
        $cmd = new GitTagDelCmd($this->input, $output);
        $cmd->run($fs->getFlags());
    }

}
