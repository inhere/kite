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
use Inhere\Kite\Console\Component\Clipboard;
use InvalidArgumentException;
use Toolkit\PFlag\FlagsParser;

/**
 * Class DemoController
 */
class JsonController extends Controller
{
    protected static $name = 'json';

    protected static $description = 'Some useful json development tool commands';

    protected static function commandAliases(): array
    {
        return [
            'toText' => ['2kv', 'to-kv', '2text']
        ];
    }

    /**
     * run a php built-in server for development(is alias of the command 'server:dev')
     *
     * @usage
     *  {command} [-S HOST:PORT]
     *  {command} [-H HOST] [-p PORT]
     *
     * @options
     *  -S          The server address. e.g 127.0.0.1:5577
     *  -H,--host   The server host address. e.g 127.0.0.1
     *  -p,--port   The server host address. e.g 5577
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function loadCommand(FlagsParser $fs, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * search keywords in the loaded JSON data.
     *
     * @arguments
     * keywords     The keywords for search
     *
     * @options
     * --type       The search type. allow: keys, path
     *
     */
    public function searchCommand(): void
    {
        $cb = Clipboard::new();

        $json = $cb->read();
        if (!$json) {
            throw new InvalidArgumentException('');
        }
    }

    /**
     * multi line JSON logs.
     */
    public function mlLogCommand(): void
    {
        $cb = Clipboard::new();

        $json = $cb->read();
        if (!$json) {
            throw new InvalidArgumentException('');
        }
    }

    /**
     * JSON to k-v text string.
     */
    public function ml2lineCommand(): void
    {
        $cb = Clipboard::new();

        $json = $cb->read();
        if (!$json) {
            throw new InvalidArgumentException('');
        }
    }

    /**
     * JSON to k-v text string.
     */
    public function toTextCommand(): void
    {
        $cb = Clipboard::new();

        $json = $cb->read();
        if (!$json) {
            throw new InvalidArgumentException('');
        }
    }
}
