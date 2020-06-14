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
use function date;

/**
 * Class DemoGroup
 */
class UtilGroup extends Controller
{
    protected static $name = 'util';

    protected static $description = 'Some useful development tool commands';

    /**
     * print current datetime
     *
     * @param Input  $input
     * @param Output $output
     */
    public function dateCommand(Input $input, Output $output): void
    {
        $output->println('Time: ' . date('Y-m-d H:i:s'));
        // $output->success('Complete');
    }

    /**
     * print system ENV information
     *
     * @options
     *  --format    Format the env value
     *
     * @arguments
     *  keywords    The keywords for search ENV
     *
     * @param Input  $input
     * @param Output $output
     */
    public function envCommand(Input $input, Output $output): void
    {
        // env | grep XXX
    }
}
