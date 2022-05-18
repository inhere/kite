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
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Common\GitLocal\GitHub;
use Inhere\Kite\Console\Component\RedirectToGitGroup;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Kite;
use PhpPkg\Http\Client\Client;
use Throwable;
use Toolkit\PFlag\FlagsParser;
use function str_contains;
use function strtoupper;

/**
 * Class GitHubGroup
 */
class GitHubController extends Controller
{
    protected static string $name = 'github';

    protected static string $desc = 'Some useful development tool commands';

    /**
     * @var array
     */
    private array $settings = [];

    /**
     * @return array
     */
    public static function aliases(): array
    {
        return ['gh', 'hub'];
    }

    protected static function commandAliases(): array
    {
        return [
            'wf'           => 'workflow',
            'rls'          => 'release',
            'pr'           => 'pullRequest',
            'redirectList' => ['rl'],
        ];
    }

    /**
     * @return string[]
     */
    protected function getOptions(): array
    {
        return [
            '--try,--dry-run' => 'bool;Dry-run the workflow, dont real execute',
            '-w, --workdir' => 'The command work dir, default is current dir.',
            // '-y, --yes' => 'Direct execution without confirmation',
            // '-i, --interactive' => 'Run in an interactive environment[TODO]',
        ];
    }

    /**
     * @return GitHub
     */
    private function getGithub(): GitHub
    {
        // $config = $this->app->getParam('github', []);
        // $github->setWorkDir($this->input->getWorkDir());

        return GitHub::new($this->output, $this->settings);
    }

    protected function beforeRun(): void
    {
        if ($this->app && !$this->settings) {
            $this->settings = Kite::config()->getArray('github');
        }
    }

    /**
     * @param string $command
     * @param array  $args
     *
     * @return bool
     * @throws Throwable
     */
    protected function onNotFound(string $command, array $args): bool
    {
        if (!$this->app) {
            return false;
        }

        $h = RedirectToGitGroup::new([
            'cmdList' => $this->settings['redirectGit'] ?? [],
        ]);

        return $h->handle($this, $command, $args);
    }

    /**
     * Show a list of commands that will be redirected to git
     *
     * @param Input  $input
     * @param Output $output
     */
    public function redirectListCommand(Input $input, Output $output): void
    {
        $output->aList([
            'current group' => self::getName(),
            'current dir'   => $input->getWorkDir(),
            'redirect cmd'  => $this->settings['redirectGit'],
        ]);
    }

    /**
     * Release new version and push to the remote github repos
     *
     * @options
     *  -b, --body          The body contents for new release. allow markdown text
     *  -m, --message       The body message for new release
     *  -v, --version       The new release tag version. e.g: v2.0.4
     *      --dry-run       Dont real send git tag and push command
     *      --last          Use the latest tag for new release
     *      --next          Auto calc next version for new release
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  see github API: https://developer.github.com/v3/repos/releases/#create-a-release
     *
     */
    public function releaseCommand(Input $input, Output $output): void
    {
        /*
{
  "tag_name": "v1.0.0",
  "target_commitish": "master",
  "name": "v1.0.0",
  "body": "Description of the release",
  "draft": false,
  "prerelease": false
}
          curl \
          -H "Accept: application/vnd.github.v3+json" \
          https://api.github.com/repos/inhere/kite/releases/tags/${tag}
          curl \
          -X POST \
          -H "Accept: application/vnd.github.v3+json" \
          -T kite-${tag}.phar
           -H "Content-Type: application/gzip" \
          https://api.github.com/repos/ingere/kite/releases/42/assets
         */

        $http = Client::factory([]);


        $output->success('Complete');
    }

    /**
     * @param Output $output
     */
    public function workflowCommand(Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * Open an github repository by browser
     *
     * @options
     *  -r, --remote         The git remote name. default is `origin`
     *      --main           bool;Use the config `mainRemote` name
     *
     * @arguments
     *  repoPath    The remote git repo URL or repository group/name.
     *              If not input, will auto parse from current work directory
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @example
     *  {fullCmd}  php-toolkit/cli-utils
     *  {fullCmd}  https://github.com/php-toolkit/cli-utils
     */
    public function openCommand(FlagsParser $fs, Output $output): void
    {
        $gh = $this->getGithub();

        // - input repoPath
        $repoPath = $fs->getArg('repoPath');
        if ($repoPath) {
            $repoUrl = $gh->parseRepoUrl($repoPath);
            if (!$repoUrl) {
                $output->error("invalid github 'repo' address: $repoPath");
                return;
            }

            AppHelper::openBrowser($repoUrl);
            $output->success('Complete');
            return;
        }

        // - auto parse
        $remote = $fs->getOpt('remote', 'origin');
        if ($fs->getOpt('main')) {
            $remote = $gh->getMainRemote();
        }

        $info = $gh->getRemoteInfo($remote);
        AppHelper::openBrowser($info->getHttpUrl());

        $output->success('Complete');
    }

    /**
     * Clone an github repository to local
     *
     * @options
     *  -g, --git        use ssh url for clone.
     *  -w, --workdir    The clone work dir, default is current dir.
     *  -m, --mirror     Clone from the mirror image host. eg: https://hub.fastgit.org/inhere/kite
     *                   allow:
     *                    fast        Clone from the https://hub.fastgit.org
     *                    cnpmjs      Clone from the https://github.com.cnpmjs.org
     *
     * @arguments
     *  repo    string;The remote git repo URL or repository name;required
     *  name    The repository name at local, default is same `repo`
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @example
     *  {binWithCmd}  php-toolkit/cli-utils
     *  {binWithCmd}  php-toolkit/cli-utils my-repo
     *  {binWithCmd}  https://github.com/php-toolkit/cli-utils
     *
     * clone from hub.fastgit.org:
     *  {binWithCmd} php-toolkit/cli-utils -m fast
     */
    public function cloneCommand(FlagsParser $fs, Output $output): void
    {
        $repo = $fs->getArg('repo');
        $name = $fs->getArg('name');

        $mirror = $fs->getOpt('mirror');
        $mirrors = [
            'fast'   => 'https://hub.fastgit.org',
            'cnpmjs' => 'https://github.com.cnpmjs.org',
        ];

        // use mirror
        if ($mirror && isset($mirrors[$mirror]) && !str_contains($repo, '//')) {
            $repo = $mirrors[$mirror] . '/' . $repo;
        }

        $gh = $this->getGithub();

        $repoUrl = $gh->parseRepoUrl($repo);
        if (!$repoUrl) {
            $output->error("invalid github 'repo' address: $repo");
            return;
        }

        $cmd = "git clone $repoUrl";
        if ($name) {
            $cmd .= " $name";
        }

        $run = CmdRunner::new($cmd);

        $workDir = $fs->getOpt('workdir');
        if ($workDir) {
            $run->setWorkDir($workDir);
        }

        $run->run(true);
        $output->success('Complete');
    }

    /**
     * generate an PR link for given project information
     *
     * @arguments
     * project      The project name.
     *
     * @options
     *  -s, --source        The source branch name. will auto prepend branchPrefix
     *      --full-source   The full source branch name
     *  -t, --target        The target branch name
     *  -o, --open          Open the generated PR link on browser
     *  -d, --direct        bool;The PR is direct from fork to main repository
     *      --new           Open new PR page on browser
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function pullRequestCommand(FlagsParser $fs, Output $output): void
    {
        $gh = $this->getGithub();
        if (!$pjName = $gh->findProjectName()) {
            $pjName = $fs->getArg('project');
            if (!$pjName) {
                throw new PromptException('project is required');
            }
        }

        $gh->loadProjectInfo($pjName);

        $p = $gh->getCurProject();

        $output->info('auto fetch current branch name');
        $curBranch = $gh->getCurBranch();
        $srcBranch = $fs->getOpt('source');
        $tgtBranch = $fs->getOpt('target');

        $brPrefix = $gh->getValue('branchPrefix', '');
        if ($srcBranch) {
            $srcBranch = $brPrefix . $srcBranch;
        } else {
            $srcBranch = $curBranch;
        }

        $open = $fs->getOpt('open');
        if ($open && strtoupper($open) === 'HEAD') {
            $open = $curBranch;
        }

        if ($tgtBranch) {
            $tgtBranch = $brPrefix . $tgtBranch;
        } elseif (is_string($open) && $open) {
            $tgtBranch = $open;
        } else {
            $tgtBranch = $curBranch;
        }

        $fGroup = $p->forkGroup;
        $ghPath = $p->getPath();

        $tipInfo = array_merge([
            'name'      => $pjName,
            'ghPath'    => $ghPath,
            'srcBranch' => $srcBranch,
            'tgtBranch' => $tgtBranch,
        ], $p->toArray());
        $output->aList($tipInfo, '- project information', ['ucFirst' => false]);

        // https://github.com/swoft-cloud/swoft-component/compare/master...ulue:dev2
        $link = $gh->getHost();
        $link .= sprintf('/%s/compare/%s...%s:%s', $ghPath, $tgtBranch, $fGroup, $srcBranch);

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
}
