<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Command;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * Class DemoCommand
 */
class InitCommand extends Command
{
    protected static $name = 'init';

    protected static $description = 'a test command';

    /**
     * do execute
     * @param  Input $input
     * @param  Output $output
     */
    protected function execute($input, $output)
    {
        // TODO copy config file
        $output->write('hello, this in ' . __METHOD__);
    }
}
