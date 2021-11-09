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
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\Clipboard;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use Inhere\Kite\Console\Component\ContentsAutoWriter;
use Inhere\Kite\Lib\Convert\JavaProperties;
use Inhere\Kite\Lib\Parser\DBTable;
use Inhere\Kite\Lib\Parser\Text\TextParser;
use Inhere\Kite\Lib\Stream\ListStream;
use InvalidArgumentException;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;
use Toolkit\FsUtil\File;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Json;
use function array_pad;
use function array_shift;
use function base_convert;
use function date;
use function file_get_contents;
use function implode;
use function is_file;
use function json_encode;
use function strlen;
use function substr;
use function trim;
use function vdump;

/**
 * Class ConvertController
 *
 * @package Inhere\Kite\Console\Controller
 */
class ConvertController extends Controller
{
    protected static $name = 'convert';

    protected static $desc = 'Some useful convert development tool commands';

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
        // $cm = new CliMarkdown();
        // $output->println($cm->parse($md));
    }

    /**
     * convert create mysql table SQL to markdown table
     *
     * @arguments
     * type     The target text doc type, allow: raw, md-table,
     *
     * @options
     *  -s,--source     string;The source code for convert. allow: FILEPATH, @clipboard;true
     *  -o,--output     The output target. default is stdout.
     *     --item-sep   The item sep char. default is NL.
     *     --value-num   int;The item value number. default get from first line.
     *     --value-sep   The item value sep char. default is SPACE
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function textCommand(FlagsParser $fs, Output $output): void
    {
        $text = $fs->getOpt('source');
        $text = ContentsAutoReader::readFrom($text);

        $p = TextParser::new($text);
        $p->setItemSep($fs->getOpt('item-sep'));
        $p->setFieldNum($fs->getOpt('value-num'));

        if ($vSep = $fs->getOpt('value-sep')) {
            $p->setItemParser(TextParser::charSplitParser($vSep));
        }

        $p->parse();

        switch ($fs->getArg('type')) {
            case 'mdtable':
            case 'mdTable':
            case 'md-table':
                $rows = ListStream::new($p->getData())
                    ->eachToArray(function (array $item) {
                        return implode(' | ', $item);
                    });
                $head = array_shift($rows);
                $line = implode('|', array_pad(['-----'], $p->fieldNum, '-----'));

                $result = $head . "\n" . $line . "\n". implode("\n", $rows);
                break;
            case 'raw':
                $result = $text;
                break;
            default:
                $result = Json::pretty($p->getData());
                break;
        }

        $outFile = $fs->getOpt('output');
        ContentsAutoWriter::writeTo($outFile, $result);
    }

    /**
     * convert YAML to java properties contents.
     *
     * @options
     *  -f,--file       The source code file. if is empty, will try read from clipboard
     *  -o,--output     string;The output target, allow: filepath, clipboard, stdout;;stdout
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function yaml2propCommand(FlagsParser $fs, Output $output): void
    {
        $file = $fs->getOpt('file');

        $str = ContentsAutoReader::readFrom($file);
        if (!$str) {
            throw new InvalidArgumentException('the source yaml contents is empty');
        }

        $parser = new Parser();
        /** @var array $data */
        $data = $parser->parse(trim($str));
        if (!$data) {
            $output->warning('empty data for convert');
            return;
        }

        $jp = new JavaProperties();

        $result  = $jp->encode($data);
        $outFile = $fs->getOpt('output');

        ContentsAutoWriter::writeTo($outFile, $result);
        $output->success('Complete');
    }

    /**
     * convert java properties to YAML contents.
     *
     * @options
     *  -f,--file       The source code file. if is empty, will try read from clipboard
     *  -o,--output     string;The output target, allow: filepath, clipboard, stdout;;stdout
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function prop2yamlCommand(FlagsParser $fs, Output $output): void
    {
        $file = $fs->getOpt('file');
        if (!$file) {
            $str = Clipboard::readAll();
        } else {
            if (!is_file($file)) {
                throw new PromptException("input source file not exists, file: $file");
            }

            $str = file_get_contents($file);
        }

        if (!$str) {
            throw new InvalidArgumentException('the source properties contents is empty');
        }

        $prop = new JavaProperties();
        $data = $prop->decode($str);
        if (!$data) {
            $output->warning('empty data for convert');
            return;
        }

        $dumper = new Dumper();
        $result = $dumper->dump($data, 1, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

        $outFile = $fs->getOpt('output');
        if (!$outFile || $outFile === 'stdout') {
            $output->println($result);
        } elseif ($outFile === 'clipboard') {
            $output->info('will send result to Clipboard');
            Clipboard::writeString($result);
        } else {
            $output->info("will write result to $outFile");
            File::putContents($outFile, $result);
        }

        $output->success('Complete');
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
