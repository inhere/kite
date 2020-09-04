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
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Common\GitLocal\GitHub;
use Inhere\Kite\Helper\AppHelper;
use PhpComp\Http\Client\Client;

/**
 * Class GitHubGroup
 */
class GitHubController extends Controller
{
    protected static $name = 'github';

    protected static $description = 'Some useful development tool commands';

    /**
     * @return array
     */
    public static function aliases(): array
    {
        return ['gh'];
    }

    protected static function commandAliases(): array
    {
        return [
            'wf'  => 'workflow',
            'rls' => 'release',
            'pr' => 'pullRequest',
        ];
    }

    /**
     * @return GitHub
     */
    private function newGithub(): GitHub
    {
        $config = $this->app->getParam('github', []);

        return GitHub::new($this->output, $config)->setWorkDir($this->input->getWorkDir());
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
     * Release new version and push to the remote github repos
     *
     * @options
     *  -b, --body          The body contents for new release. allow markdown text
     *  -m, --message       The body message for new release
     *  -v, --version       The new release tag version. e.g: v2.0.4
     *      --dry-run       Dont real send git tag and push command
     *      --last          Use the latest tag for new release
     *      --next          Auto calc next version for new release
     * @example
     *  see github API: https://developer.github.com/v3/repos/releases/#create-a-release
     *
     * @param Input  $input
     * @param Output $output
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
            'repo' => 0,
        ]);
    }

    /**
     * Open an github repository by browser
     *
     * @options
     *  -r, --remote         The git remote name. default is 'origin'
     *
     * @arguments
     *  repo    The remote git repo URL or repository group/name.
     *          If not input, will auto parse from current work directory
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
        $gh = $this->newGithub();

        $remote = $input->getSameStringOpt(['r', 'remote'], 'origin');
        $info   = $gh->parseRemote($remote)->getRemoteInfo();

        if (!empty($info['url'])) {
            AppHelper::openBrowser($info['url']);

            $output->success('Complete');
            return;
        }

        $repo = $input->getRequiredArg('repo');
        $repoUrl = $gh->parseRepoUrl($repo);
        if (!$repoUrl) {
            $output->error("invalid github 'repo' address: $repo");
            return;
        }

        AppHelper::openBrowser($repoUrl);

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

        $gh = $this->newGithub();

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
        $gh = $this->newGithub();

        // https://github.com/swoft-cloud/swoft-component/compare/master...ulue:dev2
        $pjName  = '';
        $dirName = $gh->getDirName();
        // $dirPfx  = $this->config['dirPrefix'];
        $dirPfx  = $gh->getValue('dirPrefix', '');

        // try auto parse project name for dirname.
        if ($dirPfx && strpos($dirName, $dirPfx) === 0) {
            $tmpName = substr($dirName, strlen($dirPfx));

            if (isset($this->projects[$tmpName])) {
                $pjName = $tmpName;
                $output->liteNote('auto parse project name for dirname.');
            }
        }

        if (!$pjName) {
            $pjName = $input->getRequiredArg('project');
        }

        if (!isset($this->projects[$pjName])) {
            throw new PromptException("project '{$pjName}' is not found in the config");
        }

        $output->success('Complete');
    }
}
