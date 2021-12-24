<\?php declare(strict_types=1);
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
 * Class CatCommand
 */
class CatCommand extends Command
{
    protected static string $name = 'cat';

    protected static string $desc = 'read and show contents';

    protected function configure(): void
    {
        $this->flags
              ->addOptsByRules([]);
    }

    /**
     * @param  Input $input
     * @param  Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        // do something

        $output->write('hello, this in ' . __METHOD__);
    }
}
