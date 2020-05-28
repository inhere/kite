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

    public static function ali()
    {

    }

    /**
     * run a php built-in server for development(is alias of the command 'server:dev')
     *
     * @usage
     *  {command} [-S HOST:PORT]
     *  {command} [-H HOST] [-p PORT]
     *
     * @options
     *  -S         The server address. e.g 127.0.0.1:5577
     *  -H,--host  The server host address. e.g 127.0.0.1
     *  -p,--port  The server host address. e.g 5577
     *
     * @param Input  $input
     * @param Output $output
     */
    public function serveCommand(Input $input, Output $output): void
    {
        echo "string\n";
    }

    /**
     * @arguments
     *  repo    The remote git repo URL or repo name
     *
     * @example
     *  {fullCmd}  php-toolkit/cli-utils
     *  {fullCmd}  php-toolkit/cli-utils my-repo
     */
    public function cloneCommand(): void
    {

    }
}
