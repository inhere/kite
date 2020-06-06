<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Group;

use Inhere\Console\Controller;
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Helper\GitUtil;
use function array_merge;
use function explode;
use function http_build_query;
use function in_array;
use function parse_str;
use function parse_url;
use function trim;

/**
 * Class GitLabGroup
 */
class GitLabGroup extends Controller
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
            'pr' => 'prLink',
            'li' => 'linkInfo',
        ];
    }

    protected function configure(): void
    {
        $this->config   = $this->app->getParam('gitlab', []);
        $this->projects = $this->config['projects'] ?? [];

        unset($this->config['projects']);

        parent::configure();
    }

    // protected function groupOptions(): array
    // {
    //     return [
    //         'name' => [ 'short' => '', 'desc' => '',]
    //     ];
    // }

    /**
     * parse link print information
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
     * Configure for the `linkInfoCommand`
     *
     * @param Input $input
     */
    protected function prLinkConfigure(Input $input): void
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
     *      --sync      The target branch will same source branch
     *
     * @argument
     *  project   The project key in 'gitlab' config. eg: group-name, name
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {binWithCmd} -s 4_16 -t qa          Will generate PR link for fork 'PREFIX_4_16' to main 'qa'
     *  {binWithCmd} -t qa                  Will generate PR link for fork 'HEAD_BRANCH' to main 'qa'
     *  {binWithCmd} -s 4_16 --sync         Will generate PR link for fork 'PREFIX_4_16' to main 'PREFIX_4_16'
     *  {binWithCmd} --sync                 Will generate PR link for fork 'HEAD_BRANCH' to main 'HEAD_BRANCH'
     */
    public function prLinkCommand(Input $input, Output $output): void
    {
        // http://gitlab.gongzl.com/wzl/order/merge_requests/new?utf8=%E2%9C%93&merge_request%5Bsource_project_id%5D=319&merge_request%5Bsource_branch%5D=fea_4_16&merge_request%5Btarget_project_id%5D=319&merge_request%5Btarget_branch%5D=qa

        $pjName = $input->getRequiredArg('project');
        if (!isset($this->projects[$pjName])) {
            throw new PromptException("project '{$pjName}' is not found in the config");
        }

        $pjInfo = $this->projects[$pjName];

        $link = $this->config['hostUrl'];
        $link .= "/{$pjInfo['group']}/{$pjInfo['name']}/merge_requests/new?";

        $brPrefix = $this->config['branchPrefix'];
        $fixedBrs = $this->config['fixedBranch'];

        $mainPjId = $pjInfo['mainProjectId'];
        $forkPjId = $pjInfo['forkProjectId'];

        $curBranch = GitUtil::getCurrentBranchName();
        $srcBranch = $input->getSameStringOpt(['s', 'source']);
        $tgtBranch = $input->getSameStringOpt(['t', 'target']);

        if ($srcBranch ) {
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

        $prInfo = [
            'source_project_id' => $forkPjId,
            'source_branch'     => $srcBranch,
            'target_project_id' => $mainPjId,
            'target_branch'     => $tgtBranch
        ];

        $tipInfo = array_merge([
            'project' => $pjName,
        ], $prInfo);
        $output->aList($tipInfo, 'information', ['ucFirst' => false]);
        $query = [
            'utf8'          => '✓',
            'merge_request' => $prInfo
        ];

        $link .= http_build_query($query, '', '&');

        if ($input->getSameBoolOpt(['o', 'open'])) {
            // $output->info('will auto open link on browser');
            AppHelper::openBrowser($link);
        } else {
            $output->colored("PR Link:\n  " . $link);
        }

        $output->success('Complete');
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

        [$group, $name,] = explode('/', trim($info['path'], '/'), 3);

        if (!empty($info['query'])) {
            $query = [];
            parse_str($info['query'], $query);

            $info['queryMap'] = $query;
        }

        $info['project'] = [
            'path'  => $group . '/' . $name,
            'group' => $group,
            'name'  => $name,
        ];

        $output->title('link information', ['indent' => 0]);
        $output->json($info);
    }
}
