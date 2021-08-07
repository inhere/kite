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
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
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
     * convert markdown table to create mysql table SQL
     *
     * @options
     *  -f,--file    The source markdown code
     *  -o,--output  The output target. default is stdout.
     *
     * @param Input  $input
     * @param Output $output
     */
    public function md2sqlCommand(Input $input, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * convert create mysql table SQL to markdown table
     *
     * @options
     *  -f,--file    The source markdown code
     *  -o,--output  The output target. default is stdout.
     *
     * @param Input  $input
     * @param Output $output
     */
    public function sql2mdCommand(Input $input, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * convert an mysql insert SQL to php k-v array
     *
     * @options
     *  -f,--file    The source markdown code
     *  -o,--output  The output target. default is stdout.
     *
     * @param Input  $input
     * @param Output $output
     */
    public function insertSql2arrCommand(Input $input, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * Number base conversion
     *
     * @arguments
     *  number      The want convert number.
     *
     * @options
     *  -f,--fbase      The from base value.
     *  -t,--tbase      The to base value.
     *
     * @param Input  $input
     * @param Output $output
     */
    public function baseCommand(Input $input, Output $output): void
    {
        $num = $input->getStringArg(0);

        $fBase  = $input->getSameIntOpt('f,fbase', 10);
        $toBase = $input->getSameIntOpt('t,tbase', 10);
        if ($toBase > 36) {
            throw new PromptException('to base value cannot be');
        }

        $output->colored('Result: ' . base_convert($num, $fBase, $toBase));
    }

    /**
     * convert timestamp to datetime
     *
     * @param Input  $input
     * @param Output $output
     */
    public function ts2dateCommand(Input $input, Output $output): void
    {
        $args = $input->getArguments();
        if (!$args) {
            throw new PromptException('missing arguments');
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
