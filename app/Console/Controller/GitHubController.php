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
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Common\GitLocal\GitHub;

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
        ];
    }

    /**
     * Release new version and push to the remote github repos
     *
     * @options
     *  -b, --body          The body contents for new release. allow markdown text
     *  -m, --message       The title message for new release
     *  -v, --version       The new tag version. e.g: v2.0.4
     *      --dry-run       Dont real send git tag and push command
     *      --last          Use the latest tag for new release
     *      --next          Auto calc next version for new release
     *
     * @param Input  $input
     * @param Output $output
     */
    public function releaseCommand(Input $input, Output $output): void
    {
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

        $repoUrl = GitHub::new()->getRepoUrl($repo);
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
}
