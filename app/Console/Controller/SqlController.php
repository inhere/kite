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
use Inhere\Kite\Console\Component\ContentsAutoReader;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Lib\Parser\DBTable;
use InvalidArgumentException;
use Toolkit\PFlag\FlagsParser;

/**
 * Class SqlController
 */
class SqlController extends Controller
{
    protected static string $name = 'sql';

    protected static string $desc = 'Some useful development tool commands for SQL';

    /**
     * @return string[][]
     */
    protected static function commandAliases(): array
    {
        return [
            'md' => ['2md', 'to-md'],
        ];
    }

    /**
     * convert create mysql table SQL to markdown table
     *
     * @options
     *  -s,--source     string;The source code for convert. allow: FILEPATH, @clipboard;true
     *  -o,--output     The output target. default is stdout.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function mdCommand(FlagsParser $fs, Output $output): void
    {
        $source = $fs->getOpt('source');
        $source = ContentsAutoReader::readFrom($source);

        if (!$source) {
            throw new InvalidArgumentException('empty source code for convert');
        }

        $md = DBTable::fromSchemeSQL($source)->toMDTable();
        $output->writeRaw($md);
        // $cm = new CliMarkdown();
        // $output->println($cm->parse($md));
    }

    /**
     * collect fields from create table SQL.
     *
     * @options
     *  -s, --source        string;The source create SQL for convert. allow: FILEPATH, @clipboard;true
     *  -o, --output        The output target. default is stdout.
     *      --case          Change the field name case. allow: camel, snake
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

        $toCemal = $fs->getOpt('case') === 'camel';

        $dbt = DBTable::fromSchemeSQL($source);
        $mp  = $dbt->getFieldsComments($toCemal);

        $output->aList($mp);
    }
}
