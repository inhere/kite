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
class EnvCommand extends Command
{
    protected static $name = 'env';

    protected static $description = 'a test command';

    /**
     * print system ENV information
     *
     * @options
     *  --format    Format the env value
     *
     * @arguments
     *  keywords    The keywords for search ENV
     *
     * @param  Input $input
     * @param  Output $output
     */
    protected function execute($input, $output)
    {
        $keywords = $input->getFirstArg();

        if ($keywords) {
            $upKey = strtoupper($keywords);

            if ($upKey === 'PATH') {
                $pathString = $_SERVER['PATH'] ?? '';

                $output->aList(explode(':', $pathString), 'path value', ['ucFirst' => false]);
                return;
            }

            if (isset($_SERVER[$upKey])) {
                $output->colored($_SERVER[$upKey]);

                return;
            }
        }

        // env | grep XXX
        $output->aList($_SERVER, 'ENV Information', ['ucFirst' => false]);
    }
}
