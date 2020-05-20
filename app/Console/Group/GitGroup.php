<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-10-18
 * Time: 18:58
 */

namespace Inhere\PTool\Console\Group;

use Inhere\Console\Controller;

/**
 * Class GitflowGroup
 * - git:tag:push   add tag and push to remote
 * - git:tag:delete detele the tag on remote
 *
 */
class GitGroup extends Controller
{
    protected static $name = 'gitflow';
    protected static $description = 'Some useful tool commands for git flow development';

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
