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

    /**
     * @arguments
     *  repo    The remote git repo URL or repo name
     *  name    The repo name at local
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {fullCmd}  php-toolkit/cli-utils
     *  {fullCmd}  php-toolkit/cli-utils my-repo
     */
    public function cloneCommand(Input $input, Output $output): void
    {
        $repo = $input->getFirstArg();
        $name = $input->getSecondArg();
    }
}
