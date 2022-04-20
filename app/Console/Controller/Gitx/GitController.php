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
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\Cmd;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Common\GitLocal\GitHub;
use Inhere\Kite\Console\Component\Clipboard;
use Inhere\Kite\Console\Manage\GitBranchManage;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Helper\GitUtil;
use PhpGit\Changelog\Filter\KeywordsFilter;
use PhpGit\Changelog\Formatter\GithubReleaseFormatter;
use PhpGit\Changelog\Formatter\SimpleFormatter;
use PhpGit\Changelog\GitChangeLog;
use PhpGit\Git;
use PhpGit\Info\TagsInfo;
use PhpGit\Repo;
use Throwable;
use Toolkit\Cli\Cli;
use Toolkit\Cli\Util\Clog;
use Toolkit\FsUtil\FS;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Helper\Assert;
use Toolkit\Stdlib\Obj\DataObject;
use Toolkit\Stdlib\Str;
use Toolkit\Sys\Proc\ProcTasks;
use function abs;
use function array_keys;
use function chdir;
use function implode;
use function realpath;
use function sprintf;
use function str_contains;
use function strlen;
use function strpos;
use function strtolower;
use function substr;
use function trim;

/**
 * Class GitController
 * - git:tag:push   add tag and push to remote
 * - git:tag:delete delete the tag on remote
 */
class GitController extends Controller
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
            'changelog'    => ['chlog', 'clog', 'cl'],
            'log'          => ['l', 'lg'],
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
            'branch'       => ['br'],
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
            'tagList'      => ['tag', 'tags', 'tl', 'taglist'],
            'tagInfo'      => ['tag-info', 'ti', 'tag-show'],
        ];
    }

    /**
     * @return string[]
     */
    protected function getOptions(): array
    {
        return [
            '--try,--dry-run'     => 'bool;Dry-run the workflow, dont real execute',
            '-y, --yes'     => 'bool;Direct execution without confirmation',
            '-w, --workdir' => 'The command work dir, default is current dir.',
            // '-i, --interactive' => 'Run in an interactive environment[TODO]',
        ];
    }

    protected function beforeRun(): void
    {
        if ($this->app && !isset($this->settings)) {
            $this->settings = DataObject::new($this->app->getArrayParam('git'));
        }

        if ($workdir = $this->flags->getOpt('workdir')) {
            $workdir = realpath($workdir);
            $this->output->info('Change workdir to: ' . $workdir);
            chdir($workdir);
        } else {
            $workdir = $this->input->getWorkDir();
        }

        $this->output->info("Current workdir: $workdir");
    }

    /**
     * @param string $action
     * @param array $args
     *
     * @return bool
     */
    protected function onNotFound(string $action, array $args): bool
    {
        $this->output->info("input command '$action' is not found, will exec git command: `git $action`");

        $c = Cmd::git($action);
        $c->withIf(fn() => $c->addArgs(...$args), $args);

        // if ($args) {
        //     $c->addArgs(...$args);
        // }

        $c->runAndPrint();

        return true;
    }

    /**
     * update codes from origin by git pull
     *
     * @options
     *  --dir       The want updated git repo dir. default is workdir
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
        $dir  = $fs->getOpt('dir') ?: $this->getWorkDir();
        Assert::isDir($dir . '/.git', "$dir is not a git dir");

        $c = Cmd::git('pull');
        $c->setWorkDir($dir);
        $c->setDryRun($this->flags->getOpt('dry-run'));
        $c->addArgs(...$args);
        $c->run(true);

        $output->success('Complete');
    }

    /**
     * push codes to origin by `git push`
     *
     * @options
     *  --dir       The git repo dir. default is workdir
     *
     * @arguments
     *  gitArgs  Input more args or opts for run git
     *
     * @param FlagsParser $fs
     * @param Output $output
     * @example
     * {binWithCmd} -- -u origin main  # with custom args for call git push
     */
    public function pushCommand(FlagsParser $fs, Output $output): void
    {
        $args = $fs->getRawArgs();
        $dir  = $fs->getOpt('dir') ?: $this->getWorkDir();
        Assert::isDir($dir . '/.git', "$dir is not a git dir");

        $c = Cmd::git('push')
            ->setWorkDir($dir)
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
        $repo = Repo::new();
        $repo->setPrintCmd($fs->getOpt('show-commands'));

        $output->aList($repo->getInfo(), 'Project Info', [
            'ucFirst' => false,
        ]);
    }

    /**
     * list branch by git branch
     *
     * @options
     *  -a, --all          bool;Display all branches
     *  -r, --remote       Display branches for the given remote
     *      --only-name    bool;Only display branch name
     *      --inline       bool;Only display branch name and print inline
     *  -s, --search       The keyword name for search branches
     *
     * @arguments
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function branchCommand(FlagsParser $fs, Output $output): void
    {
        $opts = [];
        $repo = Repo::new();

        $remote = '';
        $inline = $fs->getOpt('inline');
        if ($fs->getOpt('all')) {
            $opts['all'] = true;
        } elseif ($remote = $fs->getOpt('remote')) {
            $opts['remotes'] = true;
        }

        $list = $repo->getGit()->branch->getList($opts);

        $onlyName = $fs->getOpt('only-name');
        $keyword  = $fs->getOpt('search');

        $msg = 'Branch List';
        if (strlen($remote) > 1) {
            $msg .= " Of '$remote'";
        }

        if ($keyword) {
            $msg .= "(keyword: $keyword)";
        }

        $output->colored($msg . ':');

        $matched = [];
        $rmtLen  = strlen($remote) + 1;
        foreach ($list as $name => $item) {
            if ($remote) {
                $pos = strpos($name, $remote . '/');
                if ($pos !== 0) {
                    continue;
                }

                $name = substr($name, $rmtLen);
            }

            if ($keyword && !str_contains($name, $keyword)) {
                continue;
            }

            $matched[$name] = $item;
        }

        // \vdump($keyword, $remote, $list);
        if ($inline) {
            $output->println(implode(',', array_keys($matched)));
        } elseif ($onlyName) {
            $output->println(array_keys($matched));
        } else {
            $output->table($matched, 'Git Branches');
        }
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

        $gbm = new GitBranchManage();
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
     * list all git tags for the project
     *
     * @arguments
     *  keywords    Filter by input keywords
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function tagListCommand(FlagsParser $fs, Output $output): void
    {
        // git tag --sort=-creatordate 倒序排列
        $cmd = 'git tag -l -n2';
        $kw  = $fs->getArg(0);
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
     *  -n, --next              bool;Auto calc next version for add new tag.
     *  --no-auto-add-v         bool;Not auto add 'v' for add tag version.
     *
     * @param FlagsParser $fs
     * @param Input $input
     * @param Output $output
     */
    public function tagNewCommand(FlagsParser $fs, Input $input, Output $output): void
    {
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

        // $remotes = Git::new($dir)->remote->getList();
        if ($tag[0] !== 'v' && !$fs->getOpt('no-auto-add-v')) {
            $tag = 'v' . $tag;
        }

        $info = [
            'Work Dir' => $dir,
            // 'Origin'   => $remotes,
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

        if (
            $this->isInteractive() &&
            !$this->flags->getOpt('yes') &&
            $output->unConfirm('please ensure create and push new tag')
        ) {
            $output->colored('  GoodBye!');
            return;
        }

        $dryRun = $this->flags->getOpt('dry-run');

        // git tag -a $1 -m "Release $1"
        // git push origin --tags
        // $cmd = sprintf('git tag -a %s -m "%s" && git push origin %s', $tag, $msg, $tag);
        $run = CmdRunner::new();
        $run->setDryRun($dryRun);
        $run->addf('git tag -a %s -m "%s"', $tag, $msg);
        $run->addf('git push origin %s', $tag);
        $run->runAndPrint();

        $output->success('Complete');
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
     */
    public function tagDeleteCommand(FlagsParser $fs, Output $output): void
    {
        $tag = $fs->getOpt('tag');
        if (!$tag) {
            throw new PromptException('please input tag name');
        }

        $run = CmdRunner::new();
        $run->addf('git tag -d %s', $tag);

        if (false === $fs->getOpt('no-remote')) {
            $remote = $fs->getOpt('remote', 'origin');

            $run->addf('git push %s :refs/tags/%s', $remote, $tag);
        }

        $run->runAndPrint();

        $output->success('Complete');
    }

    /**
     * run git add/commit at once command
     *
     * @options
     *  -m, --message    string;The commit message;required
     *
     * @arguments
     *  files   Only add special files
     *
     * @throws Throwable
     */
    public function acCommand(): void
    {
        // $input->setLOpt('not-push', true);
        /*
         args:
         array(3) {
          [0]=> string(2) "ac"
          [1]=> string(2) "-m"
          [2]=> string(8) "some message"
        }
         */
        $args   = $this->flags->getRawArgs();
        $args[] = '--not-push';

        $this->runActionWithArgs('acp', $args);
    }

    /**
     * run git add/commit/push at once command
     *
     * @options
     *  -m, --message             string;The commit message text
     *      --nm, --no-message    bool;not input message, write message by git interactive shell.
     *      --not-push            bool;Dont execute git push
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
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function acpCommand(FlagsParser $fs, Output $output): void
    {
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

        $signText = $fs->getOpt('sign-text', $this->settings->getString('sign-text'));
        $autoSign = $fs->getOpt('auto-sign', $this->settings->getBool('auto-sign'));

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

        $run = CmdRunner::new("git status $added");
        $run->setDryRun($this->flags->getOpt('dry-run'));

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

    /**
     * display recently git commits information by `git log`
     *
     * @arguments
     *  maxCommit       int;Max display how many commits;;15
     *
     * @options
     *  --ac, --abbrev-commit     bool;Only display the abbrev commit ID
     *  --exclude                 Exclude contains given sub-string. multi by comma split.
     *  --file                    Export changelog message to file
     *  --format                  The git log option `--pretty` value.
     *                            can be one of oneline, short, medium, full, fuller, reference, email, raw, format:<string> and tformat:<string>.
     *  --mc, --max-commit        int;Max display how many commits
     *  --nc, --no-color          bool;Dont use color render git output
     *  --nm, --no-merges         bool;No contains merge request logs
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function logCommand(FlagsParser $fs, Output $output): void
    {
        $b = Git::new()->newCmd('log');

        $noColor = $fs->getOpt('no-color');
        $exclude = $fs->getOpt('exclude');

        $noMerges  = $fs->getOpt('no-merges');
        $abbrevID  = $fs->getOpt('abbrev-commit');
        $maxCommit = $fs->getOpt('max-commit', $fs->getArg('maxCommit'));

        // git log --color --graph --pretty=format:'%Cred%h%Creset:%C(ul yellow)%d%Creset %s (%Cgreen%cr%Creset, %C(bold blue)%an%Creset)' --abbrev-commit -10
        $b->add('--graph');
        $b->addIf('--color', !$noColor);
        $b->add('--pretty=format:"%Cred%h%Creset:%C(ul yellow)%d%Creset %s (%Cgreen%cr%Creset, %C(bold blue)%an%Creset)"');
        $b->addIf("--exclude=$exclude", $exclude);
        $b->addIf('--abbrev-commit', $abbrevID);
        $b->addIf('--no-merges', $noMerges);
        $b->add('-' . abs($maxCommit));

        $b->runAndPrint();

        $output->success('Complete');
    }

    /**
     * collect git change log information by `git log`
     *
     * @arguments
     *  oldVersion      string;The old version. eg: v1.0.2
     *                  - keywords `last/latest` will auto use latest tag.
     *                  - keywords `prev/previous` will auto use previous tag.;required
     *  newVersion      string;The new version. eg: v1.2.3
     *                  - keywords `head` will use `Head` commit.;required
     *
     * @options
     *  --exclude               Exclude contains given sub-string. multi by comma split.
     *  --fetch-tags            bool;Update repo tags list by `git fetch --tags`
     *  --file                  Export changelog message to file
     *  --filters               Apply built in log filters. multi by `|` split. TODO
     *                          allow:
     *                          kw     keyword filter. eg: `kw:tom`
     *                          kws    keywords filter.
     *                          ml     msg length filter.
     *                          wl     word length filter.
     *  --format                The git log option `--pretty` value.
     *                          can be one of oneline, short, medium, full, fuller, reference, email,
     *                          raw, format:<string> and tformat:<string>.
     *  -s, --style             The style for generate for changelog.
     *                          allow: markdown(<cyan>default</cyan>), simple, gh-release(ghr)
     *  --repo-url              The git repo URL address. eg: https://github.com/inhere/kite
     *                          default will auto use current git origin remote url
     *  --nm,--no-merges        bool;No contains merge request logs
     *  --unshallow             bool;Convert to a complete warehouse, useful on GitHub Action.
     *  --with-author           bool;Display commit author name
     *  --cb, --to-clipboard    bool;Copy results to clipboard
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @example
     *   {binWithCmd} last head
     *   {binWithCmd} last head --style gh-release --no-merges
     *   {binWithCmd} v2.0.9 v2.0.10 --no-merges --style gh-release --exclude "cs-fixer,format codes"
     */
    public function changelogCommand(FlagsParser $fs, Output $output): void
    {
        $builder = Git::new()->newCmd('log');
        // see https://devhints.io/git-log-format
        // useful options:
        // --no-merges
        // --glob=<glob-pattern>
        // --exclude=<glob-pattern>

        $repo = Repo::new();
        if ($fs->getOpt('fetch-tags')) {
            $fetch = $repo->newCmd('fetch', '--tags');
            // fix: fetch tags history error on github action.
            // see https://stackoverflow.com/questions/4916492/git-describe-fails-with-fatal-no-names-found-cannot-describe-anything
            $fetch->addIf('--unshallow', $fs->getOpt('unshallow'));
            $fetch->addArgs('--force');
            $fetch->runAndPrint();
        }

        // git log v1.0.7...v1.0.8 --pretty=format:'<project>/commit/%H %s' --reverse
        // git log v1.0.7...v1.0.7 --pretty=format:'<li> <a href="https://github.com/inhere/<project>/commit/%H">view commit &bull;</a> %s</li> ' --reverse
        // git log v1.0.7...HEAD --pretty=format:'<li> <a href="https://github.com/inhere/<project>/commit/%H">view commit &bull;</a> %s</li> ' --reverse
        $oldVersion = $fs->getArg('oldVersion');
        $oldVersion = $this->getLogVersion($oldVersion);

        $newVersion = $fs->getArg('newVersion');
        $newVersion = $this->getLogVersion($newVersion);

        $logFmt = GitChangeLog::LOG_FMT_HS;
        if ($fs->getOpt('with-author')) {
            // $logFmt = GitChangeLog::LOG_FMT_HSC;
            $logFmt = GitChangeLog::LOG_FMT_HSA;
        }

        $output->info('collect git log output');
        if ($oldVersion && $newVersion) {
            $builder->add("$oldVersion...$newVersion");
        }

        $builder->addf('--pretty=format:"%s"', $logFmt);

        // $b->addIf("--exclude $exclude", $exclude);
        // $b->addIf('--abbrev-commit', $abbrevID);
        $noMerges = $fs->getOpt('no-merges');
        $builder->addIf('--no-merges', $noMerges);
        $builder->add('--reverse');
        $builder->run();

        $repoUrl = $fs->getOpt('repo-url');
        if (!$repoUrl) {
            $rmtInfo = $repo->getRemoteInfo();
            $repoUrl = $rmtInfo->getHttpUrl();
        }

        $output->info('repo URL: ' . $repoUrl);

        if (!$gitLog = $builder->getOutput()) {
            $output->warning('empty git log output, quit generate');
            return;
        }

        $gcl = GitChangeLog::new($gitLog);
        $gcl->setLogFormat($logFmt);
        $gcl->setRepoUrl($repoUrl);

        if ($exclude = $fs->getOpt('exclude')) {
            $keywords = Str::explode($exclude, ',');
            $gcl->addItemFilter(new KeywordsFilter($keywords));
        }

        $style = $fs->getOpt('style');
        if ($style === 'ghr' || $style === 'gh-release') {
            $gcl->setItemFormatter(new GithubReleaseFormatter());
        } elseif ($style === 'simple') {
            $gcl->setItemFormatter(new SimpleFormatter());
        }

        // parse and generate.
        $output->info('parse logs and generate changelog');
        $gcl->generate();

        $outFile = $fs->getOpt('file');
        $output->info('total collected changelog number: ' . $gcl->getLogCount());

        if ($outFile) {
            $output->info('export changelog to file: ' . $outFile);
            $gcl->export($outFile);
            $output->success('Completed');
        } elseif ($fs->getOpt('to-clipboard')) {
            $output->info('Will send results to clipboard');
            Clipboard::new()->write($gcl->getChangelog());
        } else {
            $output->println($gcl->getChangelog());
        }
    }

    /**
     * @param string $version
     *
     * @return string
     */
    protected function getLogVersion(string $version): string
    {
        $toLower = strtolower($version);
        if ($toLower === 'head') {
            return 'HEAD';
        }

        if ($toLower === 'latest' || $toLower === 'last') {
            $version = $this->getDescSortedTags()->first();
            $this->output->info('auto find latest tag: ' . $version);
        } elseif ($toLower === 'prev' || $toLower === 'previous') {
            $version = $this->getDescSortedTags()->second();
            $this->output->info('auto find previous tag: ' . $version);
        }

        return $version;
    }

    /**
     * @var TagsInfo|null
     */
    private ?TagsInfo $tagsInfo = null;

    /**
     * @return TagsInfo
     */
    protected function getDescSortedTags(): TagsInfo
    {
        if (!$this->tagsInfo) {
            $this->tagsInfo = Git::new()->tag->tagsInfo('-version:refname');
        }

        return $this->tagsInfo;
    }
}