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
use Inhere\Console\Util\Show;
use function basename;
use function glob;
use const GLOB_MARK;

/**
 * Class FileGroup
 */
class FileGroup extends Controller
{
    protected static $name = 'file';

    protected static $description = 'Some useful development tool commands';

    public static function aliases(): array
    {
        return ['fs'];
    }

    /**
     * ls files
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
    public function lsCommand(Input $input, Output $output): void
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
            if ($filter && \preg_match("#$filter#", $line) === false) {
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

    public function vimCommand(Input $input, Output $output): void
    {
        $file = $input->getRequiredArg(0);

        $descriptors = [
            ['file', '/dev/tty', 'r'],
            ['file', '/dev/tty', 'w'],
            ['file', '/dev/tty', 'w']
        ];

        $process = proc_open("vim $file", $descriptors, $pipes);
    }
}
