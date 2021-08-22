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
use function basename;
use function glob;
use function preg_match;
use const GLOB_MARK;

/**
 * Class FileController
 */
class FileController extends Controller
{
    protected static $name = 'file';

    protected static $description = 'Some useful development tool commands';

    public static function aliases(): array
    {
        return ['fs'];
    }

    protected static function commandAliases(): array
    {
        return [
            'ls' => 'list',
            'rn' => 'rename',
        ];
    }

    /**
     * list files like linux command `ls`
     *
     * @options
     *  --file          Only display files
     *  --dir           Only display directories
     *  --only-name     Only display file/dir name
     *  --prefix        Add prefix before each path
     *  --filter        Filter match path by given string
     *
     * @arguments
     *  path  The ls path
     *
     * @param Input  $input
     * @param Output $output
     */
    public function listCommand(Input $input, Output $output): void
    {
        $path = $input->getStringArg(0);

        $filter = $input->getStringOpt('filter');
        $prefix = $input->getStringOpt('prefix');

        $onlyName = $input->getBoolOpt('only-name');

        foreach (glob($path . '/*', GLOB_MARK) as $item) {
            $line = $item;
            if ($onlyName) {
                $line = basename($item);
            }

            // filter path
            if ($filter && preg_match("#$filter#", $line) === false) {
                continue;
            }

            echo "{$prefix}$line\n";
        }
    }

    /**
     * create ln
     *
     * @options
     *  -s, --src  The server address. e.g 127.0.0.1:5577
     *  -d, --dst  The server host address. e.g 127.0.0.1
     *
     * @param Input  $input
     * @param Output $output
     */
    public function lnCommand(Input $input, Output $output): void
    {
        // ln -s "$PWD"/bin/htu /usr/local/bin/htu

        Show::success('ddd');
        // $output->success('hello');
    }

    /**
     * use vim edit an input file
     *
     * @arguments
     *  path  The ls path
     *
     * @param Input  $input
     * @param Output $output
     */
    public function vimCommand(Input $input, Output $output): void
    {
        $file = $input->bindArgument('file', 0)->getRequiredArg('file');

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
     *  -d, --dir STRING    The files directory for rename.
     *  --driver STRING     The path match driver.
     *                      allow: fn - fnmatch, reg - preg_match. (default: <cyan>fn</cyan>)
     *
     * @param Input  $input
     * @param Output $output
     */
    public function renameCommand(Input $input, Output $output): void
    {
        $output->success('hello');
    }

    /**
     * Download an remote file to local by terminal
     *
     * @arguments
     *   fileUrl   The remote file url address.
     *
     * @options
     *  -v                  Open debug mode.
     *      --pt   STRING   The progress bar type. allow: txt,bar
     *  -s, --save STRING   The save local file for downloaded.
     *
     * @param Input  $input
     * @param Output $output
     */
    public function downCommand(Input $input, Output $output): void
    {
        $url = $input->getRequiredArg(0);

        $d = Download::create($url);
        $d->setShowType($input->getStringOpt('pt', Download::PROGRESS_BAR));
        $d->setDebug($input->getBoolOpt('v'));
        $d->setSaveAs($input->getStringOpt('s,save'));
        $d->start();

        $output->success("Complete Download: $url");
    }
}
