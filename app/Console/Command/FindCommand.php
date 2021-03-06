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
        $output->info('recommended install fzf for search file');
    }
}
