<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Controller\Gitx;

use Inhere\Console\Console;
use Inhere\Console\Controller;
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\Cmd;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Common\GitLocal\GitLab;
use Inhere\Kite\Console\Attach\Gitlab\BranchCmd;
use Inhere\Kite\Console\Attach\Gitlab\BranchDeleteCmd;
use Inhere\Kite\Console\Attach\Gitlab\MergeRequestCmd;
use Inhere\Kite\Console\Attach\Gitlab\ProjectCmd;
use Inhere\Kite\Console\Component\RedirectToGitGroup;
use Inhere\Kite\Console\SubCmd\Gitflow\BranchCreateCmd;
use Inhere\Kite\Console\SubCmd\Gitflow\UpdateNoPushCmd;
use Inhere\Kite\Console\SubCmd\Gitflow\UpdatePushCmd;
use Inhere\Kite\Console\SubCmd\GitlabCmd\ResolveConflictCmd;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Kite;
use Throwable;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Str;
use function chdir;
use function date;
use function explode;
use function http_build_query;
use function in_array;
use function parse_str;
use function realpath;
use function sprintf;
use function str_contains;
use function str_starts_with;
use function trim;

/**
 * Class GitLabGroup
 */
class GitLabController extends Controller
{
    protected static string $name = 'gitlab';

    protected static string $desc = 'Some useful tool commands for gitlab development';

    /**
     * @var array
     */
    private array $settings = [];

    /**
     * @return string[]
     */
    public static function aliases(): array
    {
        return ['gl'];
    }

    protected static function commandAliases(): array
    {
        return [
                'deleteBranch' => ['del-br', 'delbr', 'dbr', 'db'],
                'newBranch'    => ['new-br', 'newbr', 'nbr', 'nb'],
                'li'           => 'linkInfo',
                'cf'           => 'config',
                'conf'         => 'config',
                'rc'           => 'resolve',
                'new'          => 'create',
                'up'           => 'update',
                'updatePush'   => ['upp', 'up-push'],
                'project'      => ['pj', 'info'],
                'checkout'     => ['co'],
            ] + [
                BranchCmd::getName()          => BranchCmd::aliases(),
                MergeRequestCmd::getName()    => MergeRequestCmd::aliases(),
                ResolveConflictCmd::getName() => ResolveConflictCmd::aliases(),
                UpdatePushCmd::getName()      => UpdatePushCmd::aliases(),
                UpdateNoPushCmd::getName()    => UpdateNoPushCmd::aliases(),
            ];
    }

    /**
     * @return array
     */
    protected function subCommands(): array
    {
        return [
            BranchCmd::class,
            ProjectCmd::class,
            MergeRequestCmd::class,
            ResolveConflictCmd::class,
            UpdatePushCmd::class,
            UpdateNoPushCmd::class,
        ];
    }

    /**
     * @return string[]
     */
    protected function getOptions(): array
    {
        return [
            '--try, --dry-run' => 'bool;Dry-run the workflow, dont real execute',
            '-y, --yes'        => 'bool;Direct execution without confirmation',
            '-w, --workdir'    => 'The command work dir, default is current dir.',
            // '-i, --interactive' => 'Run in an interactive environment[TODO]',
        ];
    }

    /**
     * @return GitLab
     */
    private function getGitlab(): GitLab
    {
        // $config = $this->app->getParam('gitlab', []);
        // $gitlab->setWorkDir($this->input->getWorkDir());

        return GitLab::new($this->output, $this->settings);
    }

    protected function beforeRun(): void
    {
        if ($this->app && !$this->settings) {
            $this->settings = Kite::config()->getArray('gitlab');
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
     * @param string $command
     * @param array $args
     *
     * @return bool
     * @throws Throwable
     */
    protected function onNotFound(string $command, array $args): bool
    {
        if (!$this->app) {
            return false;
        }

        if (in_array($command, ['ac', 'acp'], true)) {
            // TODO deny commit message.
        }

        $h = RedirectToGitGroup::new([
            'cmdList' => $this->settings['redirectGit'] ?? [],
        ]);

        return $h->handle($this, $command, $args);
    }

    /**
     * Show a list of commands that will be redirected to git
     *
     * @param Input $input
     * @param Output $output
     */
    public function redirectListCommand(Input $input, Output $output): void
    {
        $output->aList([
            'current group' => $this->getGroupName(),
            'current dir'   => $input->getWorkDir(),
            'redirect cmd'  => $this->settings['redirectGit'],
        ]);
    }

    /**
     * init a gitlab project
     *
     * @options
     *  -l, --list    bool;List all project information
     *  -e, --edit    bool;Edit the project remote info
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function initCommand(FlagsParser $fs, Output $output): void
    {
        $gl = $this->getGitlab();

        $mRemote = $gl->getMainRemote();
        $fRemote = $gl->getForkRemote();

        $output->aList([
            'main remote(source):'  => $mRemote,
            'fork remote(develop):' => $fRemote,
        ], 'Config:', ['ucFirst' => false]);

        $remotes = $gl->getRepo()->getRemotes();
        if ($remotes) {
            $output->colored('Remotes(by git):', 'ylw0');
            $output->prettyJSON($remotes);
        }

        if ($fs->getOpt('list')) {
            return;
        }

        $updated  = false;
        $doEdit   = $fs->getOpt('edit');
        $markName = 'main';
        $markMsg  = 'main(source)';

        $repo = $gl->getRepo();
        if ($repo->hasRemote($mRemote)) {
            if ($doEdit) {
                $output->colored("- update the $markMsg remote '$mRemote' url");
                $remoteUrl = $output->ask("$markName remote url:");
                if (Str\UrlHelper::isGitUrl($remoteUrl)) {
                    $updated = true;
                    // set remote url
                    $repo->getGit()->remote->url->set($mRemote, $remoteUrl);
                } else {
                    $output->warning('input is invalid url, not update');
                }
            } else {
                $output->info("$markName remote '$mRemote' exists, skip set");
            }
        } else {
            $output->colored("- the $markMsg remote '$mRemote' not exists, please set it");

            $remoteUrl = $output->ask("$markName remote url:");
            // add remote
            if (Str\UrlHelper::isGitUrl($remoteUrl)) {
                $updated = true;
                $repo->getGit()->remote->add($mRemote, $remoteUrl);
            } else {
                $output->warning('input is invalid url, not add');
            }
        }

        $markName = 'fork';
        $markMsg  = 'fork(develop)';
        if ($repo->hasRemote($fRemote)) {
            if ($doEdit) {
                $output->colored("- update the $markMsg remote '$fRemote' url");
                $remoteUrl = $output->ask("$markName remote url:");
                if (Str\UrlHelper::isGitUrl($remoteUrl)) {
                    $updated = true;
                    // set remote url
                    $repo->getGit()->remote->url->set($fRemote, $remoteUrl);
                } else {
                    $output->warning('input is invalid url, not update');
                }
            } else {
                $output->info("$markName remote '$fRemote' exists, skip set");
            }
        } else {
            $output->colored("- the $markMsg remote '$fRemote' not exists, please set it");

            $remoteUrl = $output->ask("$markName remote url:");
            // add remote
            if (Str\UrlHelper::isGitUrl($remoteUrl)) {
                $updated = true;
                $repo->getGit()->remote->add($fRemote, $remoteUrl);
            } else {
                $output->warning('input is invalid url, not add');
            }
        }

        if ($updated) {
            $remotes = $gl->getRepo()->getRemotes(true);
            $output->prettyJSON($remotes, 'Remotes(by git):');
        }

        // $fi = $gl->getRemoteInfo($gl->getForkRemote());
        // if ($fi)

        // TODO config remote
        // $output->ask($question);

        $output->colored('Complete', 'green1');
    }

    protected function initSetRemote(): void
    {

    }

    /**
     * Clone an gitlab repository to local
     *
     * @options
     *  -g, --git       bool;Use git protocol for git clone.
     *      --group     The group name.
     *
     * @arguments
     *  repo    string;The remote git repo URL or repository name;required;
     *  name    The repository name at local, default is same `repo`
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @example
     *   {fullCmd}  mylib/some-utils
     *   {fullCmd}  mylib/some-utils local-repo
     *   {fullCmd}  https://gitlab.com/some/pkg
     */
    public function cloneCommand(FlagsParser $fs, Output $output): void
    {
        $repo  = $fs->getArg('repo');
        $group = $fs->getOpt('group');
        if ($group) {
            $repo = "$group/$repo";
        }

        $useGit  = $fs->getOpt('git');
        $repoUrl = $this->getGitlab()->parseRepoUrl($repo, $useGit);
        if (!$repoUrl) {
            $output->error("invalid github 'repo' address: $repo");
            return;
        }

        // git clone $repoUrl
        $name = $fs->getArg('name');

        Cmd::git('clone')
            ->add($repoUrl)
            ->addIf($name, $name !== '')
            ->setDryRun($this->flags->getOpt('dry-run'))
            // ->setWorkDir($this->flags->getOpt('workdir')) // fix: 前已经用 chdir 更改当前目录了
            ->run(true);

        $output->success('Complete');
        $output->info('recommend run: `kite gl init` for init some information');
    }

    /**
     * checkout to new branch and update code to latest
     *
     * @arguments
     * branch       string;target branch name;true
     *
     * @options
     *  --np, --no-push      bool;dont push to origin remote after update
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function checkoutCommand(FlagsParser $fs, Output $output): void
    {
        $br = $fs->getArg('branch');
        // $repo = Repo::new();
        // $repo->hasBranch(); TODO

        $co = Cmd::git('checkout')->addArgs($br)->runAndPrint();
        if ($co->isFail()) {
            return;
        }

        $output->notice('update branch code to latest');
        $this->runUpdateByGit(!$fs->getOpt('no-push'), $output);
    }

    /**
     * show gitlab config information
     *
     * @options
     *  -l, --list    bool;List all project information
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function configCommand(FlagsParser $fs, Output $output): void
    {
        if ($fs->getOpt('list')) {
            $config = $this->getGitlab()->getConfig();
            $output->json($config);
            return;
        }

        // TODO config remote

        $output->success('Complete');
    }

    /**
     * checkout an new branch for development. alias for `br new`
     *
     * @options
     *  --nm, --not-main   bool;Dont push new branch to the main remote
     *
     * @arguments
     *  branch      The new branch name. eg: fea_6_12
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @throws Throwable
     */
    public function newBranchCommand(FlagsParser $fs, Output $output): void
    {
        $bcCmd = new BranchCreateCmd($this->input, $output);
        $bcCmd->run($fs->getFlags());
    }

    /**
     * delete branches from local, origin, main remote
     *
     * @options
     * -f, --force              bool;Force execute delete command, ignore error
     * --nm, --not-main         bool;Dont delete branch on the main remote
     *
     * @arguments
     *  branches...   array;The want deleted branch name(s). eg: fea_6_12;required
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @throws Throwable
     */
    public function deleteBranchCommand(FlagsParser $fs, Output $output): void
    {
        $bcCmd = new BranchDeleteCmd($this->input, $output);
        $bcCmd->run($fs->getFlags());
    }

    /**
     * show gitlab project config information
     *
     * @options
     *  -l, --list    bool;List all project information
     *
     * @argument
     *  name     Display project information for given name
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function projectCommand(FlagsParser $fs, Output $output): void
    {
        $gitlab = $this->getGitlab();
        if ($fs->getOpt('list')) {
            $output->json($gitlab->getProjects());
            return;
        }

        if (!$pjName = $gitlab->findProjectName()) {
            $pjName = $fs->getArg('name');
        }

        $gitlab->loadProjectInfo($pjName);
        $project = $gitlab->getCurProject();

        $output->json($project->toArray());
        $output->success('Complete');
    }

    /**
     * open gitlab project page on browser
     *
     * @options
     *  -m, --main   bool;Open the main repo page
     *
     * @arguments
     *  remote      The remote name, can also use group/repo. default: origin
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @example
     * {binWithCmd} group/repo
     */
    public function openCommand(FlagsParser $fs, Output $output): void
    {
        $gitlab = $this->getGitlab();

        $defRemote = $gitlab->getForkRemote();
        if ($fs->getOpt('main')) {
            $defRemote = $gitlab->getMainRemote();
        }

        $remote = $fs->getArg('remote', $defRemote);

        // is url path.
        if (str_contains($remote, '/')) {
            if (str_starts_with($remote, 'http')) {
                $link = $remote;
            } else {
                $link = $gitlab->getHost() . '/' . $remote;
            }
        } else {
            $info = $gitlab->getRemoteInfo($remote);
            $link = $info->getHttpUrl();
        }

        AppHelper::openBrowser($link);
        $output->success('Complete');
    }

    /**
     * parse link print information
     *
     * @arguments
     * link     Please input an gitlab link
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function linkInfoCommand(FlagsParser $fs, Output $output): void
    {
        $link = $fs->getArg('link');
        $info = Str\UrlHelper::parse2($link);

        [$group, $repo,] = explode('/', trim($info['path'] ?? '', '/'), 3);

        if (!empty($info['query'])) {
            $qStr = $info['query'];
            // $qStr  = \rawurlencode($info['query']);
            $query = [];
            parse_str($qStr, $query);

            if (isset($query['utf8'])) {
                // $query['utf8'] = '%E2%9C%93'; // ✓
                unset($query['utf8']);
                $info['query'] = http_build_query($query);
                // $info['query'] = UrlHelper::build('', $query);
            }
            $info['queryMap'] = $query;
        }

        $info['project'] = [
            'path'  => $group . '/' . $repo,
            'group' => $group,
            'repo'  => $repo,
        ];

        $output->title('link information', ['indent' => 0]);
        $output->json($info);
    }

    /**
     * sync codes from remote main repo
     *
     * @options
     *  -b, --branch  The sync code branch name, default is current branch(<info>{curBranchName}</info>)
     *  -r, --remote  The main remote name, default: {mainRemote}
     *      --push    Push to origin remote after update
     *
     * @param Input $input
     * @param Output $output
     *
     * @throws Throwable
     * @example
     *  {binWithCmd}             Sync code from the main repo remote {curBranchName} branch
     *  {binWithCmd} -b master   Sync code from the main repo remote master branch
     *
     */
    public function syncCommand(Input $input, Output $output): void
    {
        $binName = $input->getBinName();
        $output->info("TIPS:\n $binName gl:pr -o -t BRANCH");

        Console::app()->dispatch('gf:sync');
    }

    /**
     * create an new project from base project repo
     *
     * @options
     *  -g, --group         The new project main group in gitlab. if not set, will use base project group
     *  -o, --fork-group    The new project origin group in gitlab. if not set, will dont update
     *  -r, --remote        The base skeleton project repo name with group.
     *
     * @arguments
     *   name       string;The new project name.;required;
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @example
     *  {binWithCmd} new-project -r common/yii2-demo
     *  {binWithCmd} new-project -r common/yii2-demo -g new-group
     *  {binWithCmd} new-project -r go-common/demo
     *  {binWithCmd} new-project -r go-common/demo -o new-group
     */
    public function createCommand(FlagsParser $fs, Output $output): void
    {
        $name = $fs->getArg('name');
        $addr = $fs->getOpt('remote');
        if (!$addr) {
            throw new PromptException('please input the base project address by "-r|--remote"');
        }

        $gitlab  = $this->getGitlab();
        $nShorts = $gitlab->getValue('repoShorts');
        if ($nShorts && isset($nShorts[$addr])) {
            $old  = $addr;
            $addr = $nShorts[$addr];
            $output->info("find repo '$old' short name setting. use real name: $addr");
        }

        $addr = trim($addr, '/');
        if (!str_contains($addr, '/')) {
            throw new PromptException('the input base repo name is invalid. should as "GROUP/NAME"');
        }

        [$baseGroup,] = explode('/', $addr, 2);

        $group  = $fs->getOpt('group', $baseGroup);
        $gitUrl = $gitlab->getValue('gitUrl');
        if (!$gitUrl) {
            throw new PromptException('please config the "gitlab.gitUrl" address');
        }

        $workDir = $this->input->getWorkDir();
        $fGroup  = $fs->getOpt('fork-group');

        $output->aList([
            'the gitlab git url' => $gitUrl,
            'base project path'  => $addr,
            'new project name'   => $name,
            'main group name'    => $group,
            'origin group name'  => $fGroup,
        ], 'information', [
            'ucFirst' => false,
        ]);

        $run = CmdRunner::new();
        $run->setDryRun($this->flags->getOpt('dry-run'));
        $run->setWorkDir($workDir . '/' . $name);

        // $run->addf('git clone %s:%s.git %s', $gitUrl, $addr, $name);
        $run->addByArray([
            'workDir' => $workDir,
            'command' => sprintf('git clone %s:%s.git %s', $gitUrl, $addr, $name),
        ]);

        // add main
        $run->addf('git remote add main %s:%s/%s.git', $gitUrl, $group, $name);

        // reset origin
        if ($fGroup) {
            $run->addf('git remote set-url origin %s:%s/%s.git', $gitUrl, $fGroup, $name);
        }

        $run->addf('git remote -v', $name);
        $run->addf('git push main master');

        // $run->addf('git push -u origin master');
        $run->run(true);

        $output->success("Create the '$name' ok!");
    }

    /**
     * @param bool $doPush
     * @param Output $output
     *
     * @return void
     */
    protected function runUpdateByGit(bool $doPush, Output $output): void
    {
        $gitlab = $this->getGitlab();

        $curBranch = $gitlab->getCurBranch();
        $output->info('Current Branch: ' . $curBranch);

        $runner = CmdRunner::new();
        $runner->setDryRun($this->flags->getOpt('dry-run'));
        $runner->add('git pull');
        $runner->addf('git pull %s %s', $gitlab->getMainRemote(), $curBranch);

        if ($curBranch !== 'master') {
            $runner->addf('git pull %s master', $gitlab->getMainRemote());
        }

        if ($doPush) {
            $runner->add('git push origin');
        }

        $runner->run(true);
        $output->success('Complete. datetime: ' . date('Y-m-d H:i:s'));
    }
}
