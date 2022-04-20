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
use Toolkit\Cli\Color\ColorTag;
use Toolkit\FsUtil\FileFinder;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Helper\Assert;
use Toolkit\Stdlib\Std;
use function basename;
use function dirname;
use function strtr;
use function time;

/**
 * Class FindCommand
 */
class FindCommand extends Command
{
    protected static string $name = 'find';

    protected static string $desc = 'search file, dir by some options, like find';

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
     * --paths, --path, -m                  Include paths pattern. multi split by comma ','.
     * --not-paths, --np                    Exclude paths pattern. multi split by comma ','. eg: node_modules,bin
     * --names, --name                      Include file name match pattern, multi split by comma ','.
     * --not-names, --nn                    Exclude names pattern. multi split by comma ','.
     * --exclude, --ex                      Exclude pattern for dir name and each sub-dirs, multi split by comma ','.
     * --type, -t                           string;set find type, default is not limit. eg: dir, file.
     * --only-dirs, --only-dir, --od        bool;Only find dirs.
     * --only-files, --only-file, -f        bool;Only find files.
     * --exec, -e                           Exec command for each find file/dir path.
     *                                      Can used vars in command:
     *                                      - {path}    refer the found file/dir path
     *                                      - {file}    refer the found file path
     *                                      - {dir}     refer the found dir path
     *                                      - {name}    refer the found file name
     *                                      - {d_name}  refer the found dir name or file dir name.
     * --dry-run, --try                     bool;Not real run the input command by --exec.
     * ---not-ignore-vcs, --niv             bool;ignore vcs dirs, eg: .git, .svn, .hg
     * --with-dot-file, --wdf               bool;not ignore file on start with '.'
     * --with-dot-dir, --wdd                bool;not ignore dir on start with '.'
     * --not-recursive, --nr                bool;not recursive sub-dirs.
     * --dirs, --in, -d                     Find in the dirs, multi split by comma ','.
     *
     * @arguments
     * dirs          Find in the dirs, multi split by comma ','.
     * match         Include paths pattern, same of the option --paths.
     *
     * @param Input $input
     * @param Output $output
     *
     * @return int
     * @example
     * {binWithCmd} --nr -e 'cd {path}; git status' .
     */
    protected function execute(Input $input, Output $output): int
    {
        $fs = $this->flags;

        $dirs = $this->flags->getOpt('dirs', $fs->getArg('dirs'));
        // $dirs = $fs->getArg('dirs');
        Assert::notEmpty($dirs, 'dirs cannot be empty.');

        $output->info('Find in the dirs:' . Std::toString($dirs));

        $ff = FileFinder::create()->in($dirs)
            ->skipUnreadableDirs()
            ->notFollowLinks()
            ->ignoreVCS(!$fs->getOpt('not-ignore-vcs'))
            ->recursiveDir(!$fs->getOpt('not-recursive'))
            ->ignoreDotFiles(!$fs->getOpt('with-dot-file'))
            ->ignoreDotDirs(!$fs->getOpt('with-dot-dir'))
            ->exclude($fs->getOpt('exclude'))
            ->addNames($fs->getOpt('names'))
            ->notNames($fs->getOpt('not-names'))
            ->addPaths($fs->getOpt('paths', $fs->getArg('match')))
            ->notPaths($fs->getOpt('not-paths'));

        $type = $fs->getOpt('type');
        if ($type === 'dir') {
            $ff->onlyDirs();
        } elseif ($type === 'file' || $fs->getOpt('only-files')) {
            $ff->onlyFiles();
        }

        $cmdTpl = $fs->getOpt('exec');
        $output->aList($ff->getInfo());

        $cmd = Cmd::new()->setDryRun($fs->getOpt('dry-run'));
        $output->colored('RESULT:', 'ylw');

        $count = 0;
        $start = time();
        foreach ($ff->all() as $info) {
            $count++;

            $fullPath = $info->getPathname();
            // println($info->isDir() ? 'D' : 'F', $fullPath);
            $isDir = $info->isDir();
            $output->writeln(ColorTag::wrap($isDir ? 'D' : 'F', 'cyan') . " $fullPath");

            if ($cmdTpl) {
                $dirPath = $isDir ? $fullPath : dirname($fullPath);
                $cmdStr  = strtr($cmdTpl, [
                    '{path}'   => $fullPath,
                    '{file}'   => $isDir ? '' : $fullPath,
                    '{dir}'    => $dirPath,
                    '{name}'   => $info->getFilename(),
                    '{d_name}' => basename($dirPath),
                ]);

                $cmd->setCmdline($cmdStr)->runAndPrint();
            }
        }

        $diffTime = time() - $start;
        $output->println("Total: $count, Time consumption: {$diffTime}s");
        return 0;
    }
}
