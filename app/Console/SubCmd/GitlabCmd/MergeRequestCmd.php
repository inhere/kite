<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitlabCmd;

use Inhere\Console\Command;
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Helper\GitUtil;
use function array_merge;
use function http_build_query;
use function in_array;
use function is_string;
use function strtoupper;

/**
 * class MergeRequestCmd
 *
 * @author inhere
 */
class MergeRequestCmd extends Command
{
    protected static string $name = 'pullRequest';
    protected static string $desc = 'generate an PR/MR link for given project information';

    public static function aliases(): array
    {
        return ['pr', 'mr', 'merge-request'];
    }

    /**
     * generate an PR link for given project information
     *
     * @options
     *  -s, --source        The source branch name.
     *  -t, --target        The target branch name
     *  -o, --open          Set target branch and open the PR link on browser
     *  -d, --direct        bool;The PR is direct from fork to main repository
     *      --new           bool;Open new pr page on browser. eg: http://my.gitlab.com/group/repo/merge_requests/new
     *
     * @arguments
     *  project     The project name or path in self-host gitlab.
     *
     * @help
     * Special:
     *   `@`, H, HEAD - Current branch.
     *   `@s`         - Source branch refer.
     *   `@t`         - Target branch refer.
     *
     * @param Input $input
     * @param Output $output
     *
     * @return int
     * @example
     *   {binWithCmd}                       Will generate PR link for fork 'HEAD_BRANCH' to main 'HEAD_BRANCH'
     *   {binWithCmd} -o @                  Will open PR link for fork 'HEAD_BRANCH' to main 'HEAD_BRANCH' on browser
     *   {binWithCmd} -o qa                 Will open PR link for main 'HEAD_BRANCH' to main 'qa' on browser
     *   {binWithCmd} -t qa                 Will generate PR link for main 'HEAD_BRANCH' to main 'qa'
     *   {binWithCmd} -t qa --direct        Will generate PR link for fork 'HEAD_BRANCH' to main 'qa'
     *   # Will generate PR link for 'group/repo', from 'dev' to 'qa' branch
     *   {binWithCmd} -s dev -t qa group/repo
     *
     */
    protected function execute(Input $input, Output $output): int
    {
        $fs = $this->flags;
        $gl = AppHelper::newGitlab();

        $pjName = $fs->getArg('project');
        if ($pjName) {
            [$group, $repo] = \PhpGit\GitUtil::splitPath($pjName);
            $pjInfo = [
                'name'  => $repo,
                'repo'  => $repo, // default use name as repo name.
                'group' => $group,
            ];
            $gl->setCurPjInfo($pjInfo);
        } else {
            // auto parse info from workdir
            $pjName = $gl->findProjectName();
            $gl->loadProjectInfo($pjName);
        }

        $p = $gl->getCurProject();

        // $brPrefix = $gitlab->getValue('branchPrefix', '');
        // $fixedBrs = $gitlab->getValue('fixedBranch', []);
        // 这里面的分支禁止作为源分支(source)来发起PR
        $denyBrs = $gl->getValue('denyBranches', []);

        $srcPjId = $p->getForkPid();
        $tgtPjId = $p->getMainPid();

        $srcBranch = $fs->getOpt('source');
        $tgtBranch = $fs->getOpt('target');

        if (!$srcBranch) {
            $output->info('auto fetch current branch name as source branch');
            $curBranch = GitUtil::getCurrentBranchName();
            $srcBranch = $curBranch;
        } else {
            $curBranch = $srcBranch;
        }

        $open = $gl->getRealBranchName($fs->getOpt('open'));
        // if input '@', 'head', use current branch name.
        if ($open) {
            $upper = strtoupper($open);
            if ($open === '@' || $upper === 'H' || $upper === 'HEAD') {
                $open = $curBranch;
            } elseif ($open === '@s') {
                $open = $srcBranch;
            } elseif ($open === '@t') {
                $open = $tgtBranch;
            }
        }

        if (!$tgtBranch) {
            if (is_string($open) && $open) {
                $tgtBranch = $open;
            } else {
                $tgtBranch = $curBranch;
            }
        }

        $srcBranch = $gl->getRealBranchName($srcBranch);
        $tgtBranch = $gl->getRealBranchName($tgtBranch);

        // deny as an source branch
        if ($denyBrs && $srcBranch !== $tgtBranch && in_array($srcBranch, $denyBrs, true)) {
            throw new PromptException("branch '$srcBranch' dont allow as source-branch for create PR");
        }

        $repo  = $p->repo;
        $group = $p->group;

        // Is sync to remote
        $isDirect = $fs->getOpt('direct');
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
        $link = $gl->getHost();
        $link .= "/$group/$repo/merge_requests/new?";
        $link .= http_build_query($query);
        // $link = UrlHelper::build($link, $query);

        if ($open) {
            // $output->info('will auto open link on browser');
            AppHelper::openBrowser($link);
            $output->success('Complete. at ' . date('Y-m-d H:i:s'));
        } else {
            $output->colored("PR LINK: ");
            $output->writeln('  ' . $link);
            $output->colored('Complete, please open the link on browser');
        }

        return 0;
    }
}
