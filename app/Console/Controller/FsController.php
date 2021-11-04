<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Controller;

use Inhere\Console\Controller;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Console\Util\Show;
use Toolkit\Cli\Util\Download;
use Toolkit\FsUtil\Dir;
use Toolkit\PFlag\FlagsParser;
use function basename;
use function glob;
use function preg_match;
use const GLOB_MARK;

/**
 * Class FsController
 */
class FsController extends Controller
{
    protected static $name = 'fs';

    protected static $description = 'Some useful development tool commands';

    public static function aliases(): array
    {
        return ['fs', 'file', 'dir'];
    }

    protected static function commandAliases(): array
    {
        return [
            'ls' => 'list',
            'rn' => 'rename',
            'mkdir' => ['create-dir'],
            'mkSubDirs' => ['mk-subDirs', 'mk-subs'],
        ];
    }

    /**
     * list files like linux command `ls`
     *
     * @options
     *  --file          Only display files
     *  --dir           Only display directories
     *  --only-name     bool;Only display file/dir name
     *  --prefix        Add prefix before each path
     *  --filter        Filter match path by given string
     *
     * @arguments
     *  path        The ls path
     *
     * @param FlagsParser $fs
     */
    public function listCommand(FlagsParser $fs): void
    {
        $path = $fs->getArg('path');

        $filter = $fs->getOpt('filter');
        $prefix = $fs->getOpt('prefix');

        $onlyName = $fs->getOpt('only-name');

        foreach (glob($path . '/*', GLOB_MARK) as $item) {
            $line = $item;
            if ($onlyName) {
                $line = basename($item);
            }

            // filter path
            if ($filter && preg_match("#$filter#", $line) === false) {
                continue;
            }

            echo "$prefix$line\n";
        }
    }

    /**
     * create ln
     *
     * @options
     *  -s, --src   The server address. e.g 127.0.0.1:5577
     *  -d, --dst   The server host address. e.g 127.0.0.1
     *
     * @param Output $output
     */
    public function lnCommand(Output $output): void
    {
        // ln -s "$PWD"/bin/htu /usr/local/bin/htu

        Show::success('ddd');
        // $output->success('hello');
    }

    /**
     * create ln
     *
     * @options
     *  -s, --src   The server address. e.g 127.0.0.1:5577
     *  -d, --dst   The server host address. e.g 127.0.0.1
     *
     * @param Output $output
     */
    public function findCommand(Output $output): void
    {
        // ln -s "$PWD"/bin/htu /usr/local/bin/htu

        Show::success('ddd');
        // $output->success('hello');
    }

    /**
     * create directories
     *
     * @arguments
     *  dirPaths         array;The sub directory names/paths;required
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function mkdirCommand(FlagsParser $fs, Output $output): void
    {
        $dirPaths = $fs->getArg('dirPaths');

        foreach ($dirPaths as $dirPath) {
            Dir::create($dirPath);
        }

        $output->colored('OK');
    }

    /**
     * create sub directories in the parent dir.
     *
     * @arguments
     *  parentDir       The parent directory path;required
     *  subDirs         array;The sub directory names/paths.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function mkSubDirsCommand(FlagsParser $fs, Output $output): void
    {
        $parentDir = $fs->getArg('parentDir');
        $subDirs   = $fs->getArg('subDirs');

        Dir::mkSubDirs($parentDir, $subDirs, 0776);

        $output->colored('OK');
    }

    /**
     * use vim edit an input file
     *
     * @arguments
     *  file      The file path
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function vimCommand(FlagsParser $fs, Output $output): void
    {
        $file = $fs->getArg('file');

        $descriptors = [
            ['file', '/dev/tty', 'r'],
            ['file', '/dev/tty', 'w'],
            ['file', '/dev/tty', 'w']
        ];

        $process = proc_open("vim $file", $descriptors, $pipes);
        // \var_dump(proc_get_status($process));

        // if(is_resource($process))
        while(true){
            if (proc_get_status($process)['running'] === false){
                break;
            }
        }

        // \var_dump(proc_get_status($process));
        \proc_close($process);
        $output->success('Complete');
    }

    /**
     * @options
     *  -d, --dir     The files directory for rename.
     *  --driver      The path match driver.
     *                 allow: fn - fnmatch, reg - preg_match. (default: <cyan>fn</cyan>)
     *
     * @param Input  $input
     * @param Output $output
     */
    public function renameCommand(FlagsParser $fs, Output $output): void
    {
        $output->success('hello');
    }

    /**
     * Download an remote file to local by terminal
     *
     * @arguments
     *   fileUrl   string;The remote file url address;required
     *
     * @options
     *  -v            bool;Open debug mode.
     *      --pt      The progress bar type. allow: txt,bar
     *  -s, --save    The save local file for downloaded.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function downCommand(FlagsParser $fs, Output $output): void
    {
        $url = $fs->getArg('fileUrl');

        $d = Download::create($url);
        $d->setShowType($fs->getOpt('pt', Download::PROGRESS_BAR));
        $d->setDebug($fs->getOpt('v'));
        $d->setSaveAs($fs->getOpt('save'));
        $d->start();

        $output->success("Complete Download: $url");
    }
}
