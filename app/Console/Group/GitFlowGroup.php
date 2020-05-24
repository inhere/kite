<?php declare(strict_types=1);
/**
 * This file is part of PTool.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\PTool\Console\Group;

use Inhere\Console\Controller;

/**
 * Class GitFlowGroup
 */
class GitFlowGroup extends Controller
{
    protected static $name = 'gitflow';

    protected static $description = 'Some useful tool commands for git flow development';

    public static function aliases(): array
    {
        return ['git-flow', 'gf'];
    }

    /**
     * run a php built-in server for development(is alias of the command 'server:dev')
     * @usage
     *  {command} [-S HOST:PORT]
     *  {command} [-H HOST] [-p PORT]
     * @options
     *  -S         The server address. e.g 127.0.0.1:5577
     *  -H,--host  The server host address. e.g 127.0.0.1
     *  -p,--port  The server host address. e.g 5577
     */
    public function syncCommand()
    {
        echo "string\n";
    }
}
