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
use Inhere\Console\IO\Output;
use Toolkit\PFlag\FlagsParser;

/**
 * Class DemoController
 */
class DemoController extends Controller
{
    protected static string $name = 'demo';

    protected static string $desc = 'Some useful development tool commands';

    public static function isEnabled(): bool
    {
        return false;
    }

    /**
     * @return array{string: list<string>}
     */
    protected static function commandAliases(): array
    {
        return [];
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
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function serveCommand(FlagsParser $fs, Output $output): void
    {
        $output->success('Complete');
    }
}
