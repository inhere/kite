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
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\Clipboard;
use Toolkit\PFlag\FlagsParser;
use function base_convert;
use function date;
use function strlen;
use function substr;

/**
 * Class ConvertController
 *
 * @package Inhere\Kite\Console\Controller
 */
class ConvertController extends Controller
{
    protected static $name = 'convert';

    protected static $description = 'Some useful convert development tool commands';

    /**
     * @return string[]
     */
    public static function aliases(): array
    {
        return ['conv'];
    }

    /**
     * @return string[][]
     */
    protected static function commandAliases(): array
    {
        return [
            'ts2date' => [
                'tc',
                'td',
            ],
        ];
    }

    /**
     * convert input string to PHP array.
     *
     * @options
     *  --cb            bool;read source code from clipboard
     *  -f, --file      The source code file
     *  -s, --sep       The sep char for split.
     *  -o, --output    The output target. default is stdout.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function str2arrCommand(FlagsParser $fs, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * convert markdown table to create mysql table SQL
     *
     * @options
     *  --cb           bool;read source code from clipboard
     *  -f,--file      The source code file
     *  -o,--output    The output target. default is stdout.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function md2sqlCommand(FlagsParser $fs, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * convert create mysql table SQL to markdown table
     *
     * @options
     *  --cb            bool;read input from clipboard
     *  -f,--file       The source markdown code
     *  -o,--output     The output target. default is stdout.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function sql2mdCommand(FlagsParser $fs, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * convert create mysql table SQL to PHP class
     *
     * @options
     *  --cb            bool;read source code from clipboard
     *  -f,--file       The source code file
     *  -o,--output     The output target. default is stdout.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function sql2classCommand(FlagsParser $fs, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * convert an mysql INSERT SQL to php k-v array
     *
     * @options
     *  -f,--file       The source markdown code
     *  -o,--output     The output target. default is stdout.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function sql2arrCommand(FlagsParser $fs, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * convert create mysql table SQL to markdown table
     *
     * @options
     *  --cb            bool;read source code from clipboard
     *  -f,--file       The source code file
     *  -o,--output     The output target. default is stdout.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function json2classCommand(FlagsParser $fs, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * Number base conversion.
     *
     * @arguments
     *  number      int;The want convert number;required;
     *
     * @options
     *  -f,--fbase      int;The from base value.
     *  -t,--tbase      int;The to base value.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function baseCommand(FlagsParser $fs, Output $output): void
    {
        $num = $fs->getArg('num');

        $fBase  = $fs->getOpt('f,fbase', 10);
        $toBase = $fs->getOpt('t,tbase', 10);
        if ($toBase > 36) {
            throw new PromptException('to base value cannot be');
        }

        $output->colored('Result: ' . base_convert($num, $fBase, $toBase));
    }

    /**
     * convert timestamp to datetime
     *
     * @arguments
     * times    array;The want convert timestamps
     *
     * @options
     * --cb     bool;read input from clipboard
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function ts2dateCommand(FlagsParser $fs, Output $output): void
    {
        $args = $fs->getArg('times');
        if (!$args) {
            if ($fs->getOpt('cb')) {
                $text = Clipboard::new()->read();
                $args = $text ? [$text] : [];
            }

            if (!$args) {
                throw new PromptException('missing arguments');
            }
        }

        $data = [];
        foreach ($args as $time) {
            if (strlen($time) > 10) {
                $time = substr($time, 0, 10);
            }

            $data[] = [
                'timestamp' => $time,
                'datetime'  => date('Y-m-d H:i:s', (int)$time),
            ];
        }

        $output->colored('- Current Time: ' . date('Y-m-d H:i:s'));
        // opts
        $output->table($data, 'Time to date', []);
    }
}
