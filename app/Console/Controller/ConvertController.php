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
use Inhere\Kite\Lib\Convert\JavaProperties;
use InvalidArgumentException;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;
use Toolkit\FsUtil\File;
use Toolkit\PFlag\FlagsParser;
use function base_convert;
use function date;
use function file_get_contents;
use function is_file;
use function strlen;
use function substr;
use function trim;

/**
 * Class ConvertController
 *
 * @package Inhere\Kite\Console\Controller
 */
class ConvertController extends Controller
{
    protected static $name = 'convert';

    protected static $description = 'Some useful convert development tool commands';

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
     * convert input string to PHP array.
     *
     * @options
     *  --cb            bool;read source code from clipboard
     *  -f, --file      The source code file
     *  -s, --sep       The sep char for split.
     *  -o, --output    The output target. default is stdout.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function str2arrCommand(FlagsParser $fs, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * convert markdown table to create mysql table SQL
     *
     * @options
     *  --cb           bool;read source code from clipboard
     *  -f,--file      The source code file
     *  -o,--output    The output target. default is stdout.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function md2sqlCommand(FlagsParser $fs, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * convert create mysql table SQL to markdown table
     *
     * @options
     *  --cb            bool;read input from clipboard
     *  -f,--file       The source markdown code
     *  -o,--output     The output target. default is stdout.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function sql2mdCommand(FlagsParser $fs, Output $output): void
    {
        $output->success('Complete');
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
        if (!$file) {
            $str = Clipboard::readAll();
        } else {
            if (!is_file($file)) {
                throw new PromptException("input source file not exists, file: $file");
            }

            $str = file_get_contents($file);
        }

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
