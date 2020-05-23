<?php declare(strict_types=1);
/**
 * This file is part of PTool.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\PTool\Console\Command;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * Class DemoCommand
 */
class DocCommand extends Command
{
    protected static $name = 'doc';

    protected static $description = 'a test command';

    /**
     * do execute
     * @param  Input $input
     * @param  Output $output
     */
    protected function execute($input, $output)
    {
        $output->write('hello, this in ' . __METHOD__);
    }
}
