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
use Inhere\Kite\Common\Cmd;
use SplFileInfo;
use Toolkit\FsUtil\FileFinder;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Helper\Assert;
use Toolkit\Stdlib\Std;
use function println;
use function strtr;

/**
 * Class FindCommand
 */
class FindCommand extends Command
{
    protected static string $name = 'find';

    protected static string $desc = 'find file name, contents by grep,find command';

    public static function aliases(): array
    {
        return ['glob'];
    }

    protected function beforeInitFlagsParser(FlagsParser $fs): void
    {
        parent::beforeInitFlagsParser($fs);

        $fs->setStopOnFistArg(false);
    }

    /**
     * @options
     * --paths, --path               Include paths pattern. multi split by comma ','.
     * --not-paths, --np             Exclude paths pattern. multi split by comma ','. eg: node_modules,bin
     * --names, --name               Include file,dir name match pattern, multi split by comma ','.
     * --not-names, --nn             Exclude names pattern. multi split by comma ','.
     * --only-dirs, --only-dir       Only find dirs.
     * --only-files, --only-file     Only find files.
     * --dirs, --dir, -d             Find in the dirs
     * --exec, -e                    Exec command for each find file/dir path.
     * --dry-run, --try              bool;Not real run the input command by --exec
     *
     * @arguments
     * match         Include paths pattern, same of the option --paths.
     * dirs          Find in the dirs, same of the option --paths.
     *
     * @param Input $input
     * @param Output $output
     *
     * @return int
     */
    protected function execute(Input $input, Output $output): int
    {
        $fs = $this->flags;

        $dirs = $this->flags->getOpt('dirs', $fs->getArg('dirs'));
        Assert::notEmpty($dirs, 'dirs cannot be empty.');

        $output->info('Find in the dirs:' . Std::toString($dirs));

        $ff = FileFinder::create()->in($dirs)
            ->skipUnreadableDirs()
            ->notFollowLinks()
            ->addNames($fs->getOpt('names'))
            ->notNames($fs->getOpt('not-names'))
            ->addPaths($fs->getOpt('paths', $fs->getArg('match')))
            ->notPaths($fs->getOpt('not-paths'));

        if ($fs->getOpt('only-dirs')) {
            $ff->onlyDirs();
        } elseif ($fs->getOpt('only-files')) {
            $ff->onlyFiles();
        }

        $cmdStr = $fs->getOpt('exec');
        $output->aList($ff->getInfo());

        $cmd = Cmd::new()->setDryRun($fs->getOpt('dry-run'));
        $output->colored('RESULT:', 'ylw');
        $ff->each(function (SplFileInfo $info) use($cmd, $cmdStr) {
            $fullPath = $info->getPathname();
            println('F', $fullPath);
            
            if ($cmdStr) {
                $cmdStr = strtr($cmdStr, [
                    '{file}' => $fullPath,
                ]);

                $cmd->setCmdline($cmdStr)->runAndPrint();
            }
        });
        return 0;
    }
}
