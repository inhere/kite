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
use SplFileInfo;
use Toolkit\FsUtil\FileFinder;
use Toolkit\Stdlib\Helper\DataHelper;

/**
 * Class FindCommand
 */
class FindCommand extends Command
{
    protected static string $name = 'find';

    protected static string $desc = 'find file name, contents by grep,find command';

    public static function aliases(): array
    {
        return ['grep'];
    }

    /**
     * @arguments
     * paths         array;Find in the paths;true
     *
     * @param Input $input
     * @param Output $output
     *
     * @return int
     */
    protected function execute(Input $input, Output $output): int
    {
        $paths = $this->flags->getArg('paths');
        $output->info('find in the paths:' . DataHelper::toString($paths));

        $ff = FileFinder::create()->in($paths)
            ->skipUnreadableDirs()
            ->notFollowLinks()
            ->name('plugins')
            ->notPaths(['System', 'node_modules', 'bin/'])
            // ->notNames(['System', 'node_modules', 'bin/'])
            ->onlyDirs();

        $output->aList($ff->getInfo());
        $ff->each(function (SplFileInfo $info) {
            echo $info->getPathname(), "\n";
        });

        $output->info('Completed!');
        return 0;
    }
}
