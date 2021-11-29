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
class DemoCommand extends Command
{
    protected static string $name = 'demo1';

    protected static string $desc = 'a test command';

    public static function isEnabled(): bool
    {
        return false;
    }

    /**
     * do execute
     * @param  Input $input
     * @param  Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $output->write('hello, this in ' . __METHOD__);
    }
}
