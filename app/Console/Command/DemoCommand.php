<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-02-27
 * Time: 18:58
 */

namespace Inhere\PTool\Console\Command;

use Inhere\Console\Command;

/**
 * Class DemoCommand
 */
class DemoCommand extends Command
{
    protected static $name = 'test';
    protected static $description = 'a test command';

    /**
     * do execute
     * @param  \Inhere\Console\IO\Input $input
     * @param  \Inhere\Console\IO\Output $output
     * @return int
     */
    protected function execute($input, $output)
    {
        $output->write('hello, this in ' . __METHOD__);
    }
}
