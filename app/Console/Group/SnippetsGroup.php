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
 * Class SnippetsGroup
 */
class SnippetsGroup extends Controller
{
    protected static $name = 'snippet';

    protected static $description = 'Some useful development tool commands';

    /**
     * @return string[]
     */
    public static function aliases(): array
    {
        return ['snippets', 'snip'];
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
}
