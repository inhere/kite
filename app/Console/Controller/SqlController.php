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
use Inhere\Console\IO\Output;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Lib\Parser\DBCreateSQL;
use InvalidArgumentException;
use Toolkit\PFlag\FlagsParser;

/**
 * Class SqlController
 */
class SqlController extends Controller
{
    protected static $name = 'sql';

    protected static $description = 'Some useful development tool commands for SQL';

    /**
     * collect fields from create table SQL.
     *
     * @options
     *  -s, --source        string;The source code for convert. allow: FILEPATH, @clipboard;true
     *  -o, --output        The output target. default is stdout.
     *      --case          Convert the field case.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function fieldsCommand(FlagsParser $fs, Output $output): void
    {
        $source = $fs->getOpt('source');
        $source = AppHelper::tryReadContents($source);

        if (!$source) {
            throw new InvalidArgumentException('empty source code for convert');
        }

        // $obj = new SQLMarkdown();
        // $sql = $obj->toMdTable($source);

        $p = new DBCreateSQL();
        $p->parse($source);

        $mp = $p->getFieldsComments();
        $output->aList($mp);
    }
}
