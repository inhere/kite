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
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Common\GitLocal\GitHub;
use Inhere\Kite\Helper\AppHelper;
use PhpComp\Http\Client\Client;
use ReflectionException;
use function in_array;

/**
 * Class GitHubGroup
 */
class GitHubController extends Controller
{
    protected static $name = 'github';

    protected static $description = 'Some useful development tool commands';

    /**
     * @var array
     */
    private $settings = [];

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

    protected function beforeExecute(): bool
    {
        $this->loadSettings();
        return true;
    }

    protected function beforeAction(): bool
    {
        if ($this->app) {
            $action = $this->getAction();

            $loadEnvActions = $this->settings['loadEnvOn'] ?? [];
            if ($loadEnvActions && in_array($action, $loadEnvActions, true)) {
                $this->output->info(self::getName() . ' - will load osEnv setting for command: ' . $action);
                AppHelper::loadOsEnvInfo($this->app);
            }
        }

        return true;
    }

    protected function loadSettings(): void
    {
        if ($this->app && !$this->settings) {
            $this->settings = $this->app->getParam('github', []);
        }
    }

    /**
     * @return GitHub
     */
    private function getGithub(): GitHub
    {
        $config = $this->app->getParam('github', []);
        // $github->setWorkDir($this->input->getWorkDir());

        return GitHub::new($this->output, $config);
    }

    /**
     * @param string $action
     *
     * @return bool
     * @throws ReflectionException
     */
    protected function onNotFound(string $action): bool
    {
        if (!$this->app) {
            return false;
        }

        // resolve alias
        $gitCtrl = $this->app->getController(GitUseController::getName());
        $command = $gitCtrl->getRealCommandName($action);

        $redirectGitGroup = $this->settings['redirectGit'] ?? [];

        if (in_array($command, $redirectGitGroup, true)) {
            $loadEnvActions = $this->settings['loadEnvOn'] ?? [];
            if ($loadEnvActions && in_array($command, $loadEnvActions, true)) {
                $this->output->info(self::getName() . ' - load osEnv setting for command: ' . $command);
                AppHelper::loadOsEnvInfo($this->app);
            }

            $this->output->notice("will redirect to git group: `git $command`");
            Console::app()->dispatch("git:{$command}");
            return true;
        }

        return false;
    }

    protected function configure(): void
    {
        parent::configure();

        // binding arguments
        switch ($this->getAction()) {
            case 'open':
                $this->input->bindArgument('remote', 0);
                break;
            case 'project':
                $this->input->bindArgument('name', 0);
                break;
            case 'pullRequest':
                // Configure for the `pullRequestCommand`
                $this->input->bindArgument('project', 0);
                break;
        }
    }

    /**
     * @param Input  $input
     * @param Output $output
     */
    public function redirectListCommand(Input $input, Output $output): void
    {
        $this->loadSettings();
        $output->aList([
            'redirectGit' => $this->settings['redirectGit'],
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
     * @param Input  $input
     * @param Output $output
     */
    public function workflowCommand(Input $input, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * @param Input $input
     */
    protected function openConfigure(Input $input): void
    {
        $input->bindArguments([
            'repoPath' => 0,
        ]);
    }

    /**
     * Open an github repository by browser
     *
     * @options
     *  -r, --remote         The git remote name. default is `origin`
     *      --main           Use the config `mainRemote` name
     *
     * @arguments
     *  repoPath    The remote git repo URL or repository group/name.
     *              If not input, will auto parse from current work directory
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {fullCmd}  php-toolkit/cli-utils
     *  {fullCmd}  https://github.com/php-toolkit/cli-utils
     */
    public function openCommand(Input $input, Output $output): void
    {
        $gh = $this->getGithub();

        // - input repoPath
        $repoPath = $input->getStringArg('repoPath');
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
        $remote = $input->getSameStringOpt(['r', 'remote'], 'origin');
        if ($input->getBoolOpt('main')) {
            $remote = $gh->getMainRemote();
        }

        $info = $gh->getRemoteInfo($remote);
        AppHelper::openBrowser($info->getHttpUrl());

        $output->success('Complete');
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
     * Clone an github repository to local
     *
     * @arguments
     *  repo    The remote git repo URL or repository name
     *  name    The repository name at local, default is same `repo`
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {fullCmd}  php-toolkit/cli-utils
     *  {fullCmd}  php-toolkit/cli-utils my-repo
     *  {fullCmd}  https://github.com/php-toolkit/cli-utils
     */
    public function cloneCommand(Input $input, Output $output): void
    {
        $repo = $input->getRequiredArg('repo');
        $name = $input->getStringArg('name');

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

        CmdRunner::new($cmd)->do(true);

        $output->success('Complete');
    }

    /**
     * generate an PR link for given project information
     *
     * @param Input  $input
     * @param Output $output
     */
    public function pullRequestCommand(Input $input, Output $output): void
    {
        $gh = $this->getGithub();
        if (!$pjName = $gh->findProjectName()) {
            $pjName = $input->getRequiredArg('project');
        }

        // if (!$gh->hasProject($pjName)) {
        //     throw new PromptException("project '{$pjName}' is not found in the config");
        // }

        $gh->loadProjectInfo($pjName);

        $p = $gh->getCurProject();

        $open = $input->getSameOpt(['o', 'open']);

        $output->info('auto fetch current branch name');
        $curBranch = $gh->getCurBranch();
        $srcBranch = $input->getSameStringOpt(['s', 'source']);
        $tgtBranch = $input->getSameStringOpt(['t', 'target']);

        $brPrefix = $gh->getValue('branchPrefix', '');
        if ($srcBranch) {
            $srcBranch = $brPrefix . $srcBranch;
        } else {
            $srcBranch = $curBranch;
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
