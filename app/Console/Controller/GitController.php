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
use Inhere\Kite\Common\Cmd;
use Inhere\Kite\Common\CmdRunner;
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
use Toolkit\Stdlib\Obj\ConfigObject;
use Toolkit\Stdlib\Str;
use function array_keys;
use function array_values;
use function count;
use function implode;
use function sprintf;
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
    protected static $name = 'git';

    protected static $description = 'Provide useful tool commands for quick use git';

    /**
     * @var ConfigObject
     */
    private $settings;

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
    protected function groupOptions(): array
    {
        return [
            '--dry-run' => 'Dry-run the workflow, dont real execute',
            // '-y, --yes' => 'Direct execution without confirmation',
            // '-i, --interactive' => 'Run in an interactive environment[TODO]',
        ];
    }

    protected function beforeRun(): void
    {
        if ($this->app && !$this->settings) {
            $this->settings = ConfigObject::new($this->app->getParam('git', []));
        }
    }

    /**
     * @return bool
     */
    // protected function beforeAction(): bool
    // {
    //     if ($this->app) {
    //         $proxyActions = $this->settings['loadEnvOn'] ?? [];
    //         if ($proxyActions && in_array($this->getAction(), $proxyActions, true)) {
    //             AppHelper::loadOsEnvInfo($this->app);
    //         }
    //     }
    //
    //     return true;
    // }

    /**
     * @param string $action
     *
     * @return bool
     */
    protected function onNotFound(string $action): bool
    {
        $this->output->info("input command '$action' is not found, will exec git command: `git $action`");

        $run = CmdRunner::new($this->input->getFullScript());
        $run->do(true);
        return true;
    }

    /**
     * update codes from origin by git pull
     *
     * @param Input  $input
     * @param Output $output
     */
    public function updateCommand(Input $input, Output $output): void
    {
        // $flags eg: {
        //     [0]=> string(6) "git"
        //     [1]=> string(4) "pull"
        //     [2]=> string(14) "-f"
        //   }
        $args  = [];
        $flags = $input->getFlags();
        if (count($flags) > 2) {
            unset($flags[0], $flags[1]);
            $args = array_values($flags);
        }

        $c = Cmd::git('pull');
        $c->setDryRun($input->getBoolOpt('dry-run'));
        $c->addArgs(...$args);
        $c->run(true);

        // $runner = CmdRunner::new();
        // $runner->setDryRun($input->getBoolOpt('dry-run'));
        // $runner->add('git pull');
        // $runner->runAndPrint();

        $output->success('Complete');
    }

    /**
     * push codes to origin by `git push`
     *
     * @param Input  $input
     * @param Output $output
     */
    public function pushCommand(Input $input, Output $output): void
    {
        // eg: {
        //     [0]=> string(6) "github"
        //     [1]=> string(4) "push"
        //     [2]=> string(14) "--set-upstream"
        //     [3]=> string(6) "origin"
        //     [4]=> string(4) "main"
        //   }
        $args  = [];
        $flags = $input->getFlags();
        if (count($flags) > 2) {
            unset($flags[0], $flags[1]);
            $args = array_values($flags);
        }

        $c = Cmd::git('push');
        $c->setDryRun($input->getBoolOpt('dry-run'));
        $c->addArgs(...$args);
        $c->run(true);

        $output->success('Complete');
    }

    /**
     * @param Input  $input
     * @param Output $output
     */
    public function statusCommand(Input $input, Output $output): void
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
     * --show-commands  Show exec git commands
     *
     * @param Input  $input
     * @param Output $output
     */
    public function infoCommand(Input $input, Output $output): void
    {
        $repo = Repo::new();
        $repo->setPrintCmd($input->getBoolOpt('show-commands'));

        $output->aList($repo->getInfo(), 'Project Info', [
            'ucFirst' => false,
        ]);
    }

    /**
     * @param Input $input
     */
    protected function branchConfigure(Input $input): void
    {
        // $input->bindArguments(['keyword' => 0]);
    }

    /**
     * list branch by git branch
     *
     * @options
     *  -a, --all                   Display all branches
     *  -r, --remote <string>       Display given remote branches
     *      --only-name             Only display branch name
     *      --inline                Only display branch name and print inline
     *  -s, --search <string>       The keyword name for search branches
     *
     * @arguments
     *
     * @param Input  $input
     * @param Output $output
     */
    public function branchCommand(Input $input, Output $output): void
    {
        $opts = [];
        $repo = Repo::new();

        $remote = '';
        $inline = $input->getBoolOpt('inline');
        if ($input->getSameBoolOpt('a, all')) {
            $opts['all'] = true;
        } elseif ($remote = $input->getSameStringOpt('r,remote')) {
            $opts['remotes'] = true;
        }

        $list = $repo->getGit()->branch->getList($opts);

        $onlyName = $input->getBoolOpt('only-name');
        $keyword  = $input->getSameStringOpt('s,search');

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

            if ($keyword && strpos($name, $keyword) === false) {
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
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {binWithCmd}
     *  {binWithCmd} other-remote
     */
    public function branchUpdateCommand(Input $input, Output $output): void
    {
        $remote = $input->getStringArg(0, 'origin');

        $gbm = new GitBranchManage();
        $gbm->update([$remote]);

        $output->success('Complete');
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

        CmdRunner::new()->batch($commands)->runAndPrint();
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
     * @param Input  $input
     * @param Output $output
     */
    public function tagListCommand(Input $input, Output $output): void
    {
        // git tag --sort=-creatordate 倒序排列
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

        CmdRunner::new()->batch($commands)->runAndPrint();
        $output->success('Complete');
    }

    /**
     * Add new tag version and push to the remote git repos
     *
     * @options
     *  -v, --version       The new tag version. e.g: v2.0.4
     *  -m, --message       The message for add new tag.
     *  -n, --next          Auto calc next version for add new tag.
     *
     * @param Input  $input
     * @param Output $output
     */
    public function tagNewCommand(Input $input, Output $output): void
    {
        $lTag = '';
        $dir  = $input->getPwd();

        if ($input->getSameBoolOpt('n,next')) {
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
        $run->runAndPrint();

        $output->success('Complete');
    }

    /**
     * delete an local and remote tag by `git tag`
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

        $run->runAndPrint();

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
     *      --not-push   Dont execute git push
     *      --auto-sign  Auto add sign string after message.
     *      --sign-text  Dont real execute command
     *
     * @arguments
     *  files...   Only add special files
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

        $message = trim($message);
        if (strlen($message) < 3) {
            $output->liteError('the input commit message is too short');
            return;
        }

        $output->info('Work Dir: ' . $input->getPwd());

        $added = '.';
        if ($args = $input->getArguments()) {
            $added = implode(' ', $args);
        }

        $signText = $input->getStringOpt('sign-text', $this->settings->getString('sign-text'));
        $autoSign = $input->getBoolOpt('auto-sign', $this->settings->getBool('auto-sign'));

        // will auto fetch user info by git
        if ($autoSign && !$signText) {
            $git       = Git::new();
            $username  = $git->config->get('user.name');
            $userEmail = $git->config->get('user.email');
            // eg "Signed-off-by: inhere <in.798@qq.com>"
            if ($username && $userEmail) {
                $signText = "$username <$userEmail>";
            }
        }

        if ($signText) {
            $message .= "\n\nSigned-off-by: $signText";
        }

        $run = CmdRunner::new("git status $added");
        $run->setDryRun($input->getBoolOpt('dry-run'));

        $run->do(true);
        $run->afterOkDo("git add $added");
        $run->afterOkDo(sprintf('git commit -m "%s"', $message));

        if (false === $input->getBoolOpt('not-push')) {
            $run->afterOkDo('git push');
        }

        $output->success('Complete');
    }

    /**
     * @param Input $input
     */
    protected function logConfigure(Input $input): void
    {
        $input->bindArguments([
            'maxCommit' => 0,
        ]);
    }

    /**
     * display recently git commits information by `git log`
     *
     * @arguments
     *  maxCommit       Max display how many commits
     *
     * @options
     *  --abbrev-commit     Only display the abbrev commit ID
     *  --exclude           Exclude contains given sub-string. multi by comma split.
     *  --file              Export changelog message to file
     *  --format            The git log option `--pretty` value.
     *                      can be one of oneline, short, medium, full, fuller, reference, email, raw, format:<string> and tformat:<string>.
     *  --max-commit        Max display how many commits
     *  --no-color          Dont use color render git output
     *  --no-merges         No contains merge request logs
     *
     * @param Input  $input
     * @param Output $output
     */
    public function logCommand(Input $input, Output $output): void
    {
        $b = Git::new()->newCmd('log');

        $noColor = $input->getBoolOpt('no-color');
        $exclude = $input->getStringOpt('exclude');

        $noMerges  = $input->getBoolOpt('no-merges');
        $abbrevID  = $input->getBoolOpt('abbrev-commit');
        $maxCommit = $input->getIntOpt('max-commit', $input->getIntArg('maxCommit', 15));

        // git log --color --graph --pretty=format:'%Cred%h%Creset:%C(ul yellow)%d%Creset %s (%Cgreen%cr%Creset, %C(bold blue)%an%Creset)' --abbrev-commit -10
        $b->add('--graph');
        $b->addIf('--color', !$noColor);
        $b->add('--pretty=format:"%Cred%h%Creset:%C(ul yellow)%d%Creset %s (%Cgreen%cr%Creset, %C(bold blue)%an%Creset)"');
        $b->addIf("--exclude=$exclude", $exclude);
        $b->addIf('--abbrev-commit', $abbrevID);
        $b->addIf('--no-merges', $noMerges);
        $b->add("-$maxCommit");

        $b->runAndPrint();

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
     *                - keywords `last/latest` will auto use latest tag.
     *                - keywords `prev/previous` will auto use previous tag.
     *  newVersion   The new version. eg: v1.2.3
     *                - keywords `head` will use `Head` commit.
     *
     * @options
     *  --exclude           Exclude contains given sub-string. multi by comma split.
     *  --fetch-tags        Update repo tags list by `git fetch --tags`
     *  --file              Export changelog message to file
     *  --filters           Apply built in log filters. multi by `|` split
     *                      allow:
     *                       kw     keyword filter. eg: `kw:tom`
     *                       kws    keywords filter.
     *                       ml     msg length filter.
     *                       wl     word length filter.
     *  --format            The git log option `--pretty` value.
     *                      can be one of oneline, short, medium, full, fuller, reference, email, raw, format:<string> and tformat:<string>.
     *  --style             The style for generate for changelog.
     *                      allow: markdown(<cyan>default</cyan>), simple, gh-release
     *  --repo-url          The git repo URL address. eg: https://github.com/inhere/kite
     *                      default will auto use current git origin remote url
     *  --no-merges         No contains merge request logs
     *  --unshallow         Convert to a complete warehouse, useful on GitHub Action.
     *  --with-author       Display commit author name
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {binWithCmd} last head
     *  {binWithCmd} last head --style gh-release --no-merges
     *  {binWithCmd} v2.0.9 v2.0.10 --no-merges --style gh-release --exclude "cs-fixer,format codes"
     */
    public function changelogCommand(Input $input, Output $output): void
    {
        $builder = Git::new()->newCmd('log');
        // see https://devhints.io/git-log-format
        // useful options:
        // --no-merges
        // --glob=<glob-pattern>
        // --exclude=<glob-pattern>

        $repo = Repo::new();
        if ($input->getBoolOpt('fetch-tags')) {
            $fetch = $repo->newCmd('fetch', '--tags');
            // fix: fetch tags history error on github action.
            // see https://stackoverflow.com/questions/4916492/git-describe-fails-with-fatal-no-names-found-cannot-describe-anything
            $fetch->addIf('--unshallow', $input->getBoolOpt('unshallow'));
            $fetch->addArgs('--force');
            $fetch->runAndPrint();
        }

        // git log v1.0.7...v1.0.8 --pretty=format:'<project>/commit/%H %s' --reverse
        // git log v1.0.7...v1.0.7 --pretty=format:'<li> <a href="https://github.com/inhere/<project>/commit/%H">view commit &bull;</a> %s</li> ' --reverse
        // git log v1.0.7...HEAD --pretty=format:'<li> <a href="https://github.com/inhere/<project>/commit/%H">view commit &bull;</a> %s</li> ' --reverse
        $oldVersion = $input->getRequiredArg('oldVersion');
        $oldVersion = $this->getLogVersion($oldVersion);

        $newVersion = $input->getRequiredArg('newVersion');
        $newVersion = $this->getLogVersion($newVersion);

        $logFmt = GitChangeLog::LOG_FMT_HS;
        if ($input->getBoolOpt('with-author')) {
            // $logFmt = GitChangeLog::LOG_FMT_HSC;
            $logFmt = GitChangeLog::LOG_FMT_HSA;
        }

        $output->info('collect git log output');
        $builder->add("$oldVersion...$newVersion");
        $builder->addf('--pretty=format:"%s"', $logFmt);

        // $b->addIf("--exclude $exclude", $exclude);
        // $b->addIf('--abbrev-commit', $abbrevID);
        $noMerges = $input->getBoolOpt('no-merges');
        $builder->addIf('--no-merges', $noMerges);
        $builder->add('--reverse');
        $builder->run();

        $repoUrl = $input->getStringOpt('repo-url');
        if (!$repoUrl) {
            $info = $repo->getRemoteInfo();

            $repoUrl = $info->getHttpUrl();
        }

        $output->info('repo URL: ' . $repoUrl);

        $gcl = GitChangeLog::new($builder->getOutput());
        $gcl->setLogFormat($logFmt);
        $gcl->setRepoUrl($repoUrl);

        if ($exclude = $input->getStringOpt('exclude')) {
            $keywords = Str::explode($exclude, ',');
            $gcl->addItemFilter(new KeywordsFilter($keywords));
        }

        $style = $input->getStringOpt('style');
        if ($style === 'gh-release') {
            $gcl->setItemFormatter(new GithubReleaseFormatter());
        } elseif ($style === 'simple') {
            $gcl->setItemFormatter(new SimpleFormatter());
        }

        // parse and generate.
        $output->info('parse logs and generate changelog');
        $gcl->generate();

        $outFile = $input->getStringOpt('file');
        $output->info('total collected changelog number: ' . $gcl->getLogCount());

        if ($outFile) {
            $output->info('export changelog to file: ' . $outFile);
            $gcl->export($outFile);
            $output->success('Completed');
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
     * @var TagsInfo
     */
    private $tagsInfo;

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
