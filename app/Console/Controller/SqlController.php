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
use Inhere\Kite\Lib\Parser\DBTable;
use InvalidArgumentException;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Helper\Assert;
use Toolkit\Stdlib\Json;
use function array_combine;
use function array_map;
use function count;
use function explode;
use function trim;

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
            'md'    => ['2md', 'to-md'],
            'toMap' => ['2map', '2kv', 'tomap', 'to-map'],
        ];
    }

    /**
     * convert create mysql table SQL to markdown table
     *
     * @options
     *  -s,--source     string;The source code for convert. allow: FILEPATH, @clipboard;true
     *  -o,--output     The output target. default is stdout.
     *  -l,--lang       The output language. allow:zh-CN,en;false;zh-CN
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

        $md = DBTable::fromSchemeSQL($source)
            ->setLang($fs->getOpt('lang'))
            ->toMDTable();
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
        $source = ContentsAutoReader::readFrom($source);

        if (!$source) {
            throw new InvalidArgumentException('empty source code for convert');
        }

        $toCemal = $fs->getOpt('case') === 'camel';

        $dbt = DBTable::fromSchemeSQL($source);
        $mp  = $dbt->getFieldsComments($toCemal);

        $output->aList($mp);
    }

    /**
     * convert insert SQL to k-v map and dump it.
     *
     * @options
     *  -s, --source        string;The source create SQL for convert. allow: FILEPATH, @clipboard;true
     *  -o, --output        The output target. default is stdout.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function toMapCommand(FlagsParser $fs, Output $output): void
    {
        $source = $fs->getOpt('source');
        $source = ContentsAutoReader::readFrom($source);

        $fieldAndVals = explode(' VALUES ', trim($source), 2);
        Assert::equals(count($fieldAndVals), 2, "invalid insert SQL: $source");

        $fieldStr = trim($fieldAndVals[0]);
        $valueStr = trim($fieldAndVals[1]);

        $fields = explode(',', trim($fieldStr, ' ()'));
        [, $first] = explode('(', $fields[0], 2);
        $fields[0] = trim($first);

        $fields = array_map(static fn($field) => trim(trim($field), ' `'), $fields);

        // split values
        $values = explode(', ', trim($valueStr, ' ();'));
        $values = array_map(static fn($value) => trim($value), $values);

        $map = array_combine($fields, $values);
        $output->writeRaw(Json::pretty($map));
    }
}
