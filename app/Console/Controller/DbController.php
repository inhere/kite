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
use Toolkit\PFlag\FlagsParser;
use function basename;
use function glob;
use function preg_match;
use const GLOB_MARK;

/**
 * Class DbController
 */
class DbController extends Controller
{
    protected static $name = 'db';

    protected static $description = 'Database development tool commands';

    /**
     * convert an mysql table create SQL to markdown table
     *
     * @options
     *  -s, --source    The source sql file
     *  -o, --output    The output content file
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function sql2mdCommand(FlagsParser $fs, Output $output): void
    {
        # code...
    }

    /**
     * convert an markdown table to mysql table create SQL
     *
     * @options
     *  -s, --source    The source markdown file
     *  -o, --output    The output sql file
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function md2sqlCommand(FlagsParser $fs, Output $output): void
    {
        # code...
    }
}