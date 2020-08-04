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
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Helper\GitUtil;
use ReflectionException;
use function array_merge;
use function explode;
use function http_build_query;
use function in_array;
use function parse_str;
use function parse_url;
use function strlen;
use function strpos;
use function substr;
use function trim;

/**
 * Class GitLabGroup
 */
class GitLabController extends Controller
{
    protected static $name = 'gitlab';

    protected static $description = 'Some useful tool commands for gitlab development';

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var array
     */
    private $projects = [];

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
            'pr'   => 'pullRequest',
            'li'   => 'linkInfo',
            'cf'   => 'config',
            'conf' => 'config',
        ];
    }

    protected function configure(): void
    {
        $this->config   = $this->app->getParam('gitlab', []);
        $this->projects = $this->config['projects'] ?? [];

        unset($this->config['projects']);

        parent::configure();
    }

    private function gitlab(): GitLab
    {
        $config = $this->app->getParam('gitlab', []);
        $gitlab = GitLab::new($this->output, $config);
        $gitlab->setWorkDir($this->input->getWorkDir());

        return $gitlab;
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

        $repoUrl = $this->gitlab()->getRepoUrl($repo);
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

    // protected function groupOptions(): array
    // {
    //     return [
    //         'name' => [ 'short' => '', 'desc' => '',]
    //     ];
    // }

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
            $config = $this->gitlab()->getConfig();
            $output->json($config);
            return;
        }

        $output->success('Complete');
    }

    /**
     * run git add/commit/push at once command
     *
     * @options
     *  -m, --message   The commit message
     *
     * @param Input  $input
     * @param Output $output
     *
     * @throws ReflectionException
     */
    public function acpCommand(Input $input, Output $output): void
    {
        $binName = $input->getBinName();

        Console::app()->dispatch('git:acp');

        $output->info("TIPS:\n $binName gl:pr -o -t BRANCH");
    }

    /**
     * show gitlab project config information
     *
     * @options
     *  -l, --list    List all project information
     *
     * @param Input  $input
     * @param Output $output
     */
    public function projectCommand(Input $input, Output $output): void
    {
        if ($input->getSameBoolOpt(['l', 'list'])) {
            $output->json($this->projects);
            return;
        }

        $output->success('Complete');
    }


    /**
     * open gitlab project page on browser
     *
     * @options
     *  -l, --list    List all project information
     *
     * @param Input  $input
     * @param Output $output
     */
    public function openCommand(Input $input, Output $output): void
    {
        if ($input->getSameBoolOpt(['l', 'list'])) {
            $output->json($this->projects);
            return;
        }

        $output->success('Complete');
    }


    /**
     * Resolve git conflicts
     *
     * @param Input  $input
     * @param Output $output
     */
    public function resolveCommand(Input $input, Output $output): void
    {
        // ...
        // kite gl:pr

        $output->success('Complete');
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
     *  -s, --source    The source branch
     *  -t, --target    The target branch
     *  -o, --open      Open the generated PR link on browser
     *      --direct    The PR is from fork to main repository
     *      --new       Open new pr page on browser http://my.gitlab.com/group/repo/merge_requests/new
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
        $git = $this->gitlab();

        if (!$pjName = $git->findPjName()) {
            $pjName = $input->getRequiredArg('project');
        }

        if (!$git->hasProject($pjName)) {
            throw new PromptException("project '{$pjName}' is not found in the config");
        }

        $git->setCurPjName($pjName)->loadCurPjInfo();

        $pjInfo = $git->getCurPjInfo();

        $group = $pjInfo['group'] ?? $this->config['defaultGroup'];
        $repo  = $pjInfo['repo'];

        $brPrefix = $this->config['branchPrefix'];
        $fixedBrs = $this->config['fixedBranch'];

        $srcPjId = $pjInfo['forkProjectId'];
        $tgtPjId = $pjInfo['mainProjectId'];

        $output->info('auto fetch current branch name');
        $curBranch = GitUtil::getCurrentBranchName();
        $srcBranch = $input->getSameStringOpt(['s', 'source']);
        $tgtBranch = $input->getSameStringOpt(['t', 'target']);

        if ($srcBranch) {
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
        } else {
            $tgtBranch = $curBranch;
        }

        // Is sync to remote
        $isDirect = $input->getBoolOpt('direct');
        if ($isDirect || $srcBranch === $tgtBranch) {
            $group = $pjInfo['forkGroup'] ?? $this->config['defaultForkGroup'];
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
            'project' => $pjName,
            'glPath'  => "$group/$repo",
        ], $prInfo);
        $output->aList($tipInfo, '- project information', ['ucFirst' => false]);
        $query = [
            'utf8'          => '✓',
            'merge_request' => $prInfo
        ];

        $link = $this->config['hostUrl'];
        $link .= "/{$group}/{$repo}/merge_requests/new?";
        $link .= http_build_query($query, '', '&');

        if ($input->getSameBoolOpt(['o', 'open'])) {
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
     * @options
     *  --config    Convert to config data TODO
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
}
