<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Controller;

use Inhere\Console\Component\Formatter\JSONPretty;
use Inhere\Console\Controller;
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\Clipboard;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use Inhere\Kite\Console\Component\ContentsAutoWriter;
use Inhere\Kite\Helper\KiteUtil;
use Inhere\Kite\Lib\Convert\JavaProperties;
use Inhere\Kite\Lib\Parser\DBTable;
use InvalidArgumentException;
use PhpPkg\Config\ConfigUtil;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Yaml;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Json;
use function base_convert;
use function date;
use function strlen;
use function substr;

/**
 * Class ConvertController
 *
 * @package Inhere\Kite\Console\Controller
 */
class ConvertController extends Controller
{
    protected static string $name = 'convert';

    protected static string $desc = 'Some useful convert development tool commands';

    /**
     * @return string[]
     */
    public static function aliases(): array
    {
        return ['conv'];
    }

    /**
     * @return string[][]
     */
    protected static function commandAliases(): array
    {
        return [
            'ts2date'   => [
                'tc',
                'td',
            ],
            'yaml2json' => ['yml2json', 'y2j'],
            'yaml2prop' => ['yml2prop', 'y2p'],
            'prop2yaml' => ['prop2yml', 'p2y'],
        ];
    }

    /**
     * convert markdown table to create mysql table SQL
     *
     * @options
     *  -s,--source     string;The source code for convert. allow: FILEPATH, @clipboard;true
     *  -o,--output    The output target. default is stdout.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function md2sqlCommand(FlagsParser $fs, Output $output): void
    {
        $source = $fs->getOpt('source');
        $source = ContentsAutoReader::readFrom($source);

        if (!$source) {
            throw new InvalidArgumentException('empty source code for convert');
        }

        $sql = DBTable::fromMdTable($source)->toCreateSQL();
        $output->writeRaw($sql);
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
    public function sql2mdCommand(FlagsParser $fs, Output $output): void
    {
        $source = $fs->getOpt('source');
        $source = ContentsAutoReader::readFrom($source);

        if (!$source) {
            throw new InvalidArgumentException('empty source code for convert');
        }

        $md = DBTable::fromSchemeSQL($source)->toMDTable();
        $output->writeRaw($md);
    }

    /**
     * convert YAML to JSON contents.
     *
     * @options
     *  -s,--source    The source code. allow: @i,@c,filepath. if is empty, will try read from STDIN
     *  -o,--output     string;The output target, allow: filepath, clipboard, stdout;;stdout
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function yaml2jsonCommand(FlagsParser $fs, Output $output): void
    {
        $str  = ContentsAutoReader::readFrom($fs->getOpt('source'));
        $data = ConfigUtil::parseYamlString($str);
        if (!$data) {
            $output->warning('empty data for convert');
            return;
        }

        $echoTip = true;
        $outFile = $fs->getOpt('output');
        if (KiteUtil::isStdoutAlias($outFile)) {
            $echoTip = false;
            $result  = JSONPretty::pretty($data);
        } else {
            $result = Json::pretty($data);
        }

        ContentsAutoWriter::writeTo($outFile, $result, ['printTips' => $echoTip]);
    }

    /**
     * convert YAML to java properties contents.
     *
     * @options
     *  -s,--source    The source code. allow: @i,@c,filepath. if is empty, will try read from STDIN
     *  -o,--output     string;The output target, allow: filepath, clipboard, stdout;;stdout
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function yaml2propCommand(FlagsParser $fs, Output $output): void
    {
        $str  = ContentsAutoReader::readFrom($fs->getOpt('source'));
        $data = ConfigUtil::parseYamlString($str);
        if (!$data) {
            $output->warning('empty data for convert');
            return;
        }

        $jp = new JavaProperties();

        $result  = $jp->encode($data);
        $outFile = $fs->getOpt('output');
        $echoTip = !KiteUtil::isStdoutAlias($outFile);

        ContentsAutoWriter::writeTo($outFile, $result, ['printTips' => $echoTip]);
    }

    /**
     * convert java properties to YAML contents.
     *
     * @options
     *  -s,--source    The source code. allow: @i,@c,filepath. if is empty, will try read from STDIN
     *  -o,--output     string;The output target, allow: filepath, clipboard, stdout;;stdout
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function prop2yamlCommand(FlagsParser $fs, Output $output): void
    {
        $str  = ContentsAutoReader::readFrom($fs->getOpt('source'));
        $prop = new JavaProperties();
        $data = $prop->decode($str);

        if (!$data) {
            $output->warning('empty data for convert');
            return;
        }

        $dumper = new Dumper();
        $result = $dumper->dump($data, 1, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

        $outFile = $fs->getOpt('output');
        $echoTip = !KiteUtil::isStdoutAlias($outFile);

        ContentsAutoWriter::writeTo($outFile, $result, ['printTips' => $echoTip]);
    }

    /**
     * Number base conversion.
     *
     * @arguments
     *  number      string;The want convert number string;required;
     *
     * @options
     *  -f,--fbase      int;The from base value.
     *  -t,--tbase      int;The to base value.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function baseCommand(FlagsParser $fs, Output $output): void
    {
        $num = $fs->getArg('number');

        $fBase  = $fs->getOpt('fbase', 10);
        $toBase = $fs->getOpt('tbase', 10);
        if ($toBase > 36) {
            throw new PromptException('to base value cannot be > 36');
        }

        $converted = base_convert($num, $fBase, $toBase);
        $output->colored("Result: $converted, len: " . strlen($converted));
    }

    /**
     * convert timestamp to datetime
     *
     * @arguments
     * times    array;The want convert timestamps
     *
     * @options
     * --cb     bool;read input from clipboard
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function ts2dateCommand(FlagsParser $fs, Output $output): void
    {
        $args = $fs->getArg('times');
        if (!$args) {
            if ($fs->getOpt('cb')) {
                $text = Clipboard::new()->read();
                $args = $text ? [$text] : [];
            }

            if (!$args) {
                throw new PromptException('missing arguments');
            }
        }

        $data = [];
        foreach ($args as $time) {
            if (strlen($time) > 10) {
                $time = substr($time, 0, 10);
            }

            $data[] = [
                'timestamp' => $time,
                'datetime'  => date('Y-m-d H:i:s', (int)$time),
            ];
        }

        $output->colored('- Current Time: ' . date('Y-m-d H:i:s'));
        // opts
        $output->table($data, 'Time to date', []);
    }
}
