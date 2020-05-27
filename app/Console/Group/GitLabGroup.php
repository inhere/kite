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
 * Class GitLabGroup
 */
class GitLabGroup extends Controller
{
    protected static $name = 'gitlab';

    protected static $description = 'Some useful tool commands for gitlab development';

    /**
     * @return array|string[]
     */
    public static function aliases(): array
    {
        return ['gl'];
    }

    // protected function groupOptions(): array
    // {
    //     return [
    //         'name' => [ 'short' => '', 'desc' => '',]
    //     ];
    // }

    public function prLinkCommand(): void
    {

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
