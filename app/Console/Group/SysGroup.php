<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Group;

use Inhere\Console\Controller;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * Class DemoGroup
 */
class SysGroup extends Controller
{
    protected static $name = 'sys';

    protected static $description = 'Some useful tool commands for system';

    /**
     * run a php built-in server for development(is alias of the command 'server:dev')
     *
     * @param Input  $input
     * @param Output $output
     */
    public function serveCommand(Input $input, Output $output): void
    {
        $output->success('Complete');
    }
}
