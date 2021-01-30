<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Controller;

use Inhere\Console\Console;
use Inhere\Console\Controller;
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Common\GitLocal\GitLab;
use Inhere\Kite\Console\Attach\Gitlab\ProjectInit;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Helper\GitUtil;
use ReflectionException;
use function array_merge;
use function explode;
use function http_build_query;
use function in_array;
use function is_string;
use function parse_str;
use function parse_url;
use function sprintf;
use function strpos;
use function trim;

/**
 * Class GitLabGroup
 */
class GitLabController extends Controller
{
    protected static $name = 'gitlab';

    protected static $description = 'Some useful tool commands for gitlab development';

    /**
     * @return array|string[]
     */
    public static function aliases(): array
    {
        return ['gl'];
    }

    protected static function commandAliases(): array
    {
        return [
            'pr'      => 'pullRequest',
            'li'      => 'linkInfo',
            'cf'      => 'config',
            'conf'    => 'config',
            'nb'      => 'newBranch',
            'db'      => 'deleteBranch',
            'rc'      => 'resolve',
            'up'      => 'update',
            'upp'     => 'updatePush',
            'new'     => 'create',
            'project' => ['pj', 'info'],
        ];
    }

    /**
     * @return array
     */
    protected function commands(): array
    {
        return [
            'test' => function() {
                \printf("hello \n");
            },
            ProjectInit::class,
        ];
    }

    /**
     * @return string[]
     */
    protected function groupOptions(): array
    {
        return [
            '--dry-run' => 'Dry-run the workflow, dont real execute',
            '-y, --yes' => 'Direct execution without confirmation',
            // '-i, --interactive' => 'Run in an interactive environment[TODO]',
        ];
    }

    protected function configure(): void
    {
        parent::configure();

        // simple binding arguments
        switch ($this->getAction()) {
            case 'open':
                $this->input->bindArgument('remote', 0);
                break;
            case 'project':
                $this->input->bindArgument('name', 0);
                break;
        }
    }

    /**
     * @return GitLab
     */
    private function getGitlab(): GitLab
    {
        $config = $this->app->getParam('gitlab', []);
        // $gitlab->setWorkDir($this->input->getWorkDir());

        return GitLab::new($this->output, $config);
    }

    /**
     * init an gitlab project
     *
     * @options
     *  -l, --list    List all project information
     *  -e, --edit    Edit the project remote info
     *
     * @param Input  $input
     * @param Output $output
     */
    public function initCommand(Input $input, Output $output): void
    {
        $gl = $this->getGitlab();

        $mRemote = $gl->getMainRemote();
        $fRemote = $gl->getForkRemote();

        $output->aList([
            'main remote(source)'  => $mRemote,
            'fork remote(for dev)' => $fRemote,
        ], 'Config:', ['ucFirst' => false]);

        $remotes = $gl->getRepo()->getRemotes();
        $output->colored('Remotes(by git):', 'ylw0');
        $output->json($remotes);

        if ($input->getSameBoolOpt('l,list')) {
            return;
        }

        if (!$gl->getRepo()->hasRemote($fRemote)) {
            $output->colored('- the fork(develop) remote not exists, please set it');

            $url = $output->ask('forked remote url');
            if (!$url) {
                return;
            }
        }

        if (!$gl->getRepo()->hasRemote($fRemote)) {
            $output->colored('- the fork(develop) remote not exists, please set it');

            $url = $output->ask('forked remote url');
            if (!$url) {
                return;
            }
        }

        $fi = $gl->getRemoteInfo($gl->getForkRemote());
        // if ($fi)

        // TODO config remote
        // $output->ask($question);

        $output->colored('Complete', 'green1');
    }

    /**
     * @param Input $input
     */
    protected function cloneConfigure(Input $input): void
    {
        $input->bindArguments([
            'repo' => 0,
            'name' => 1,
        ]);
    }

    /**
     * Clone an gitlab repository to local
     *
     * @options
     *  --git    Use git protocol for git clone.
     *
     * @arguments
     *  repo    The remote git repo URL or repository name
     *  name    The repository name at local, default is same `repo`
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {fullCmd}  mylib/some-utils
     *  {fullCmd}  mylib/some-utils local-repo
     *  {fullCmd}  https://gitlab.com/some/pkg
     */
    public function cloneCommand(Input $input, Output $output): void
    {
        $repo = $input->getRequiredArg('repo');

        $useGit  = $input->getBoolOpt('git');
        $repoUrl = $this->getGitlab()->parseRepoUrl($repo, $useGit);
        if (!$repoUrl) {
            $output->error("invalid github 'repo' address: $repo");
            return;
        }

        $cmd = "git clone $repoUrl";
        if ($name = $input->getStringArg('name')) {
            $cmd .= " $name";
        }

        CmdRunner::new($cmd)->do(true);

        $output->success('Complete');
    }

    /**
     * show gitlab config information
     *
     * @options
     *  -l, --list    List all project information
     *
     * @param Input  $input
     * @param Output $output
     */
    public function configCommand(Input $input, Output $output): void
    {
        if ($input->getSameBoolOpt(['l', 'list'])) {
            $config = $this->getGitlab()->getConfig();
            $output->json($config);
            return;
        }

        // TODO config remote

        $output->success('Complete');
    }

    /**
     * run git add/commit/push at once command
     *
     * @options
     *  -m, --message   The commit message
     *      --not-push  Dont execute git push
     *
     * @arguments
     *  files...   Only add special files
     *
     * @param Input  $input
     * @param Output $output
     *
     * @throws ReflectionException
     */
    public function acpCommand(Input $input, Output $output): void
    {
        $binName = $input->getBinName();

        $output->notice("will redirect to command: git:acp");

        Console::app()->dispatch('git:acp');

        $output->info("TIPS:\n $binName gl:pr -o -t BRANCH");
    }

    /**
     * checkout an new branch for development
     *
     * @options
     *  --not-main   Dont push new branch to the main remote
     *
     * @arguments
     *  branch      The new branch name. eg: fea_6_12
     *
     * @param Input  $input
     * @param Output $output
     *
     * @throws ReflectionException
     */
    public function newBranchCommand(Input $input, Output $output): void
    {
        $cmdName = $input->getCommand();
        /** @see GitFlowController::newBranchCommand() */
        $command = 'gitflow:newBranch';

        $output->notice("input $cmdName, will redirect to $command");

        Console::app()->dispatch($command);
    }

    /**
     * delete branches from local, origin, main remote
     *
     * @options
     *  -f, --force      Force execute delete command, ignore error
     *      --not-main   Dont delete branch on the main remote
     *
     * @arguments
     *  branches...   The want deleted branch name(s). eg: fea_6_12
     *
     * @param Input  $input
     * @param Output $output
     */
    public function deleteBranchCommand(Input $input, Output $output): void
    {
        $names = $input->getArgs();
        if (!$names) {
            throw new PromptException('please input an branch name');
        }

        $gitlab  = $this->getGitlab();
        $force   = $input->getSameBoolOpt(['f', 'force']);
        $notMain = $input->getBoolOpt('not-main');
        $dryRun  = $input->getBoolOpt('dry-run');

        $mainRemote = $gitlab->getMainRemote();
        foreach ($names as $name) {
            $run = CmdRunner::new();
            $run->setDryRun($dryRun);

            $output->title("delete the branch:{$name}", [
                'indent' => 0,
            ]);
            if ($force) {
                $run->setIgnoreError(true);
            }

            $run->addf('git branch --delete %s', $name);
            // git push origin --delete BRANCH
            $run->addf('git push origin --delete %s', $name);

            if (false === $notMain) {
                // git push main --delete BRANCH
                $run->addf('git push %s --delete %s', $mainRemote, $name);
            }

            $run->run(true);
        }

        $output->success('Completed');
    }

    /**
     * show gitlab project config information
     *
     * @options
     *  -l, --list    List all project information
     *
     * @argument
     *  name     Display project information for given name
     *
     * @param Input  $input
     * @param Output $output
     */
    public function projectCommand(Input $input, Output $output): void
    {
        $gitlab = $this->getGitlab();
        if ($input->getSameBoolOpt(['l', 'list'])) {
            $output->json($gitlab->getProjects());
            return;
        }

        if (!$pjName = $gitlab->findProjectName()) {
            $pjName = $input->getArg('name');
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
     *  -m, --main   Open the main repo page
     *
     * @argument
     *  remote     The remote name. default: origin
     *
     * @param Input  $input
     * @param Output $output
     */
    public function openCommand(Input $input, Output $output): void
    {
        $gitlab = $this->getGitlab();

        $defRemote = $gitlab->getForkRemote();
        if ($input->getSameBoolOpt(['m', 'main'])) {
            $defRemote = $gitlab->getMainRemote();
        }

        $remote = $input->getArg('remote', $defRemote);

        $info = $gitlab->getRemoteInfo($remote);
        $link = $info->getHttpUrl();

        AppHelper::openBrowser($link);

        $output->success('Complete');
    }

    /**
     * Configure for the `resolveCommand`
     *
     * @param Input $input
     */
    protected function resolveConfigure(Input $input): void
    {
        $input->bindArgument('branch', 0);
    }

    /**
     * Resolve conflicts preparing for current git branch.
     *
     * 1. will checkout to <cyan>branch</cyan>
     * 2. will update code by <cyan>git pull</cyan>
     * 3. update the <cyan>branch</cyan> codes from main repository
     * 4. merge current-branch codes from main repository
     * 5. please resolve conflicts by tools or manual
     *
     * @arguments
     *    <cyan>branch</cyan>  The conflicts target branch name. eg: testing, qa, pre
     *
     * @options
     *  --dry-run    Dry-run the workflow
     *
     * @param Input  $input
     * @param Output $output
     */
    public function resolveCommand(Input $input, Output $output): void
    {
        $gitlab = $this->getGitlab();
        $branch = $input->getRequiredArg('branch');
        $dryRun = $input->getBoolOpt('dry-run');

        $curBranch = $gitlab->getCurBranch();
        $orgRemote = $gitlab->getForkRemote();

        $runner = CmdRunner::new();
        $runner->setDryRun($dryRun);
        $runner->add('git fetch');
        $runner->addf('git checkout %s', $branch);
        // git checkout --track origin/BRANCH
        // $runner->addf('git checkout --track %s/%s', $orgRemote, $branch);
        $runner->addf('git pull');
        $runner->addf('git pull %s %s', $gitlab->getMainRemote(), $branch);
        $runner->addf('git pull %s %s', $gitlab->getMainRemote(), $curBranch);
        $runner->run(true);

        $output->success('Complete. please resolve conflicts by tools or manual');
        $output->note([
            'TIPS can exec this command after resolved for quick commit:',
            "git add . && git commit && git push && kite gl:pr -o && git checkout $curBranch"
        ]);
    }

    /**
     * Configure for the `pullRequestCommand`
     *
     * @param Input $input
     */
    protected function pullRequestConfigure(Input $input): void
    {
        $input->bindArgument('project', 0);
    }

    /**
     * generate an PR link for given project information
     *
     * @options
     *  -s, --source        The source branch name. will auto prepend branchPrefix
     *      --full-source   The full source branch name
     *  -t, --target        The target branch name
     *  -o, --open          Open the generated PR link on browser
     *  -d, --direct        The PR is direct from fork to main repository
     *      --new           Open new pr page on browser http://my.gitlab.com/group/repo/merge_requests/new
     *
     * @argument
     *  project   The project key in 'gitlab' config. eg: group-name, name
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {binWithCmd}                       Will generate PR link for fork 'HEAD_BRANCH' to main 'HEAD_BRANCH'
     *  {binWithCmd} -s 4_16 -t qa         Will generate PR link for main 'PREFIX_4_16' to main 'qa'
     *  {binWithCmd} -t qa                 Will generate PR link for main 'HEAD_BRANCH' to main 'qa'
     *  {binWithCmd} -t qa  --direct       Will generate PR link for fork 'HEAD_BRANCH' to main 'qa'
     */
    public function pullRequestCommand(Input $input, Output $output): void
    {
        // http://gitlab.my.com/group/repo/merge_requests/new?utf8=%E2%9C%93&merge_request%5Bsource_project_id%5D=319&merge_request%5Bsource_branch%5D=fea_4_16&merge_request%5Btarget_project_id%5D=319&merge_request%5Btarget_branch%5D=qa
        $gitlab = $this->getGitlab();
        if (!$pjName = $gitlab->findProjectName()) {
            $pjName = $input->getRequiredArg('project');
        }

        $gitlab->loadProjectInfo($pjName);

        $p = $gitlab->getCurProject();

        $brPrefix = $gitlab->getValue('branchPrefix', '');
        $fixedBrs = $gitlab->getValue('fixedBranch', []);
        // 这里面的分支禁止作为源分支(source)来发起PR
        $denyBrs = $gitlab->getValue('denyBranches', []);

        $srcPjId = $p->getForkPid();
        $tgtPjId = $p->getMainPid();

        $open = $input->getSameOpt(['o', 'open']);

        $output->info('auto fetch current branch name');
        $curBranch = GitUtil::getCurrentBranchName();
        $srcBranch = $input->getSameStringOpt(['s', 'source']);
        $tgtBranch = $input->getSameStringOpt('t,target');

        if ($fullSBranch = $input->getStringOpt('full-source')) {
            $srcBranch = $fullSBranch;
        } elseif ($srcBranch) {
            if (!in_array($srcBranch, $fixedBrs, true)) {
                $srcBranch = $brPrefix . $srcBranch;
            }
        } else {
            $srcBranch = $curBranch;
        }

        if ($tgtBranch) {
            if (!in_array($tgtBranch, $fixedBrs, true)) {
                $tgtBranch = $brPrefix . $tgtBranch;
            }
        } elseif (is_string($open) && $open) {
            $tgtBranch = $open;
        } else {
            $tgtBranch = $curBranch;
        }

        $srcBranch = $gitlab->getRealBranchName($srcBranch);
        $tgtBranch = $gitlab->getRealBranchName($tgtBranch);

        // deny as an source branch
        if ($denyBrs && $srcBranch !== $tgtBranch && in_array($srcBranch, $denyBrs, true)) {
            throw new PromptException("the branch '{$srcBranch}' dont allow as source-branch for PR to other branch");
        }

        $repo  = $p->repo;
        $group = $p->group;

        // Is sync to remote
        $isDirect = $input->getSameBoolOpt(['d', 'direct']);
        if ($isDirect || $srcBranch === $tgtBranch) {
            $group = $p->getForkGroup();
        } else {
            $srcPjId = $tgtPjId;
        }

        $prInfo = [
            'source_project_id' => $srcPjId,
            'source_branch'     => $srcBranch,
            'target_project_id' => $tgtPjId,
            'target_branch'     => $tgtBranch
        ];

        $tipInfo = array_merge([
            'name'   => $pjName,
            'glPath' => "$group/$repo",
        ], $prInfo);
        $output->aList($tipInfo, '- project information', ['ucFirst' => false]);
        $query = [
            'utf8'          => '✓',
            'merge_request' => $prInfo
        ];

        // $link = $this->config['hostUrl'];
        $link = $gitlab->getHost();
        $link .= "/{$group}/{$repo}/merge_requests/new?";
        $link .= http_build_query($query, '', '&');

        if ($open) {
            // $output->info('will auto open link on browser');
            AppHelper::openBrowser($link);
            $output->success('Complete');
        } else {
            $output->colored("PR LINK: ");
            $output->writeln('  ' . $link);
            $output->colored('Complete, please open the link on browser');
        }
    }

    /**
     * Configure for the `linkInfoCommand`
     *
     * @param Input $input
     */
    protected function linkInfoConfigure(Input $input): void
    {
        $input->bindArgument('link', 0);
    }

    /**
     * parse link print information
     *
     * @arguments
     * link     Please input an gitlab link
     *
     * @param Input  $input
     * @param Output $output
     */
    public function linkInfoCommand(Input $input, Output $output): void
    {
        $link = $input->getRequiredArg('link');
        $info = (array)parse_url($link);

        [$group, $repo,] = explode('/', trim($info['path'], '/'), 3);

        if (!empty($info['query'])) {
            $qStr = $info['query'];
            // $qStr  = \rawurlencode($info['query']);
            $query = [];
            parse_str($qStr, $query);

            if (isset($query['utf8'])) {
                // $query['utf8'] = '%E2%9C%93'; // ✓
                unset($query['utf8']);
                $info['query'] = http_build_query($query, '', '&');
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
     * @param Input  $input
     * @param Output $output
     *
     * @throws ReflectionException
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
     * Configure for the `createCommand`
     *
     * @param Input $input
     */
    protected function createConfigure(Input $input): void
    {
        $input->bindArgument('name', 0);
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
     *   name       The new project name.
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {binWithCmd} new-project -r go-common/demo
     *  {binWithCmd} new-project -r go-common/demo  -g new-group
     *  {binWithCmd} new-project -r common/yii2-demo
     *  {binWithCmd} new-project -r go-common/demo -o xiajianjun-go
     */
    public function createCommand(Input $input, Output $output): void
    {
        $name = $input->getRequiredArg('name');
        $addr = $input->getSameStringOpt(['r', 'remote']);
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
        if (strpos($addr, '/') === false) {
            throw new PromptException('the input base repo name is invalid. should as "GROUP/NAME"');
        }

        [$baseGroup,] = explode('/', $addr, 2);

        $group = $input->getSameStringOpt(['g', 'group'], $baseGroup);

        $gitUrl = $gitlab->getValue('gitUrl');
        if (!$gitUrl) {
            throw new PromptException('please config the "gitlab.gitUrl" address');
        }

        $workDir = $input->getWorkDir();
        $fGroup  = $input->getSameStringOpt(['o', 'fork-group'], '');

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
        $run->setDryRun($input->getBoolOpt('dry-run'));
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
     * update codes from origin and main remote repositories, then push to remote
     *
     * @options
     *      --dry-run   Dry run workflow
     *
     * @param Input  $input
     * @param Output $output
     */
    public function updatePushCommand(Input $input, Output $output): void
    {
        // do push
        $input->setSOpt('p', true);

        $this->updateCommand($input, $output);
    }

    /**
     * update codes from origin and main remote repositories
     *
     * @options
     *  -p, --push      Push to origin remote after update
     *
     * @param Input  $input
     * @param Output $output
     */
    public function updateCommand(Input $input, Output $output): void
    {
        $gitlab = $this->getGitlab();

        $curBranch = $gitlab->getCurBranch();

        $runner = CmdRunner::new();
        $runner->setDryRun($input->getBoolOpt('dry-run'));
        $runner->add('git pull');
        $runner->addf('git pull %s %s', $gitlab->getMainRemote(), $curBranch);

        if ($curBranch !== 'master') {
            $runner->addf('git pull %s master', $gitlab->getMainRemote());
        }

        if ($input->getSameBoolOpt(['p', 'push'])) {
            $runner->add('git push');
        }

        $runner->run(true);
        $output->success('Complete');
    }
}
