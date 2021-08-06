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
}
