<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-02-27
 * Time: 18:58
 */

namespace Inhere\PTool\Console\Command;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * Class FindCommand
 */
class FindCommand extends Command
{
    protected static $name = 'find';
    protected static $description = 'find file content by grep command';

    public static function aliases(): array
    {
        return ['grep'];
    }

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
