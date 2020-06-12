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
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Common\GitLocal\GitHub;

/**
 * Class GitHubGroup
 */
class GitHubGroup extends Controller
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

    public function workflowCommand(): void
    {

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
