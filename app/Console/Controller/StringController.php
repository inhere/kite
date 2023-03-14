<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Controller;

use Inhere\Console\Component\Formatter\Table;
use Inhere\Console\Controller;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\Clipboard;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use Inhere\Kite\Console\Component\ContentsAutoWriter;
use Inhere\Kite\Console\SubCmd\ConvCmd\Ts2dateCmd;
use Inhere\Kite\Console\SubCmd\ParseUrlQueryCmd;
use Inhere\Kite\Console\SubCmd\ToolCmd\HashCommand;
use Inhere\Kite\Console\SubCmd\ToolCmd\RandomCommand;
use Inhere\Kite\Helper\KiteUtil;
use Inhere\Kite\Kite;
use Inhere\Kite\Lib\Parser\Text\Json5ItemParser;
use Inhere\Kite\Lib\Parser\Text\TextParser;
use Inhere\Kite\Lib\Stream\ListStream;
use Inhere\Kite\Lib\Stream\StringStream;
use InvalidArgumentException;
use Throwable;
use Toolkit\FsUtil\File;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Arr;
use Toolkit\Stdlib\Json;
use Toolkit\Stdlib\Str;
use function array_combine;
use function array_pad;
use function array_shift;
use function count;
use function explode;
use function implode;
use function is_file;
use function preg_replace;
use function str_contains;
use function str_replace;
use function str_split;
use function strlen;
use function substr;
use function trim;

/**
 * Class StringController
 */
class StringController extends Controller
{
    protected static string $name = 'string';

    protected static string $desc = 'Some useful development tool commands';

    /**
     * @var string
     */
    private string $str;

    /**
     * @var string
     */
    private string $dumpfile;

    public static function aliases(): array
    {
        return ['str', 'text'];
    }

    protected static function commandAliases(): array
    {
        return [
            'join'    => ['implode', 'j'],
            'split'   => ['s'],
            'process' => ['p', 'filter', 'f'],
            'replace' => ['r'],
            'parse'   => ['fields'],
            'length'  => ['len', 'ln', 'count'],
        ];
    }

    protected function init(): void
    {
        parent::init();

        $this->dumpfile = Kite::getTmpPath('string-loaded.txt');
    }

    protected function subCommands(): array
    {
        return [
            Ts2dateCmd::class,
            ParseUrlQueryCmd::class,
            HashCommand::class,
            RandomCommand::class,
        ];
    }

    /**
     * @throws Throwable
     */
    private function loadDumpContents(): void
    {
        $dumpfile = $this->dumpfile;
        if (!$dumpfile || !is_file($dumpfile)) {
            throw new InvalidArgumentException("the temp file '$dumpfile' is not exists");
        }

        $this->str = File::readAll($dumpfile);
    }

    /**
     * load string data from clipboard to an tmp file
     *
     * @options
     *   --show       bool;show the loaded data contents
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @throws Throwable
     */
    public function loadCommand(FlagsParser $fs, Output $output): void
    {
        if ($fs->getOpt('show')) {
            $this->loadDumpContents();
            $output->println($this->str);
            return;
        }

        $str = Clipboard::new()->read();
        if (!$str) {
            throw new InvalidArgumentException('the clipboard data is empty');
        }

        $len = File::putContents($this->dumpfile, $str);
        $output->success('Complete.');
        $output->colored('- loaded length: ' . $len);
        $output->colored('- dumpfile path: ' . $this->dumpfile);
    }

    /**
     * Join multi line text
     *
     * @arguments
     * text     The source text for handle.
     *          Special:
     *          input '@c' or '@cb' or '@clipboard' - will read from Clipboard
     *          input empty or '@i' or '@stdin'     - will read from STDIN
     *          input '@l' or '@load'               - will read from loaded file
     *          input '@FILEPATH'                   - will read from the filepath
     *
     * @options
     *  -s, --sep    The join separator char. Defaults to an empty string.
     *
     * @param FlagsParser $fs
     */
    public function joinCommand(FlagsParser $fs): void
    {
        $text = trim($fs->getArg('text'));
        $text = ContentsAutoReader::readFrom($text, [
            'loadedFile' => $this->dumpfile,
        ]);

        $lines = Str::explode($text, "\n");
        $sep   = $fs->getOpt('sep');

        echo implode($sep, $lines), "\n";
    }

    /**
     * Split text to multi line
     *
     * @arguments
     * text     The source text for handle.
     *          Special:
     *          input '@c' or '@cb' or '@clipboard' - will read from Clipboard
     *          input empty or '@i' or '@stdin'     - will read from STDIN
     *          input '@l' or '@load'               - will read from loaded file
     *          input '@FILEPATH'                   - will read from the filepath
     *
     * @options
     *  -s, --sep           The separator char. defaults is an SPACE.
     *  --len, --length     int;Split by length.
     *  --join-sep          The sep char for join all items for output. defaults: NL
     *  -f, --filter        array;apply there filters for each substr.
     *                      allow:
     *                      - wrap eg `wrap:'` wrap char(') for each item
     *
     * @param FlagsParser $fs
     *
     * @example
     *
     *   {binWithCmd} -s '\n' @c
     *   {binWithCmd} --sep ',' --join-sep ', ' -f "wrap:'" 'tom,john' # Output: 'tom', 'john'
     */
    public function splitCommand(FlagsParser $fs): void
    {
        $text = trim($fs->getArg('text'));
        $text = ContentsAutoReader::readFrom($text);

        $len = $fs->getOpt('length');
        if ($len > 0) {
            $items = str_split($text, $len);
        } else {
            $sep = $fs->getOpt('sep', ' ');
            $sep = KiteUtil::resolveSep($sep);

            $items = explode($sep, $text);
        }

        if ($filters = $fs->getOpt('filter')) {
            foreach ($items as &$item) {
                $item = $this->applyFilters($item, $filters);
            }
            unset($item);
        }

        $joinSep = $fs->getOpt('join-sep', "\n");
        echo implode(KiteUtil::resolveSep($joinSep), $items), "\n";
    }

    /**
     * apply some simple built in string filters
     *
     * @param string $str
     * @param array $filters
     *
     * @return string
     */
    protected function applyFilters(string $str, array $filters): string
    {
        foreach ($filters as $filter) {
            if ('' === $str) {
                break;
            }

            $args   = [];
            $argStr = '';

            // eg 'wrap:,'
            if (str_contains($filter, ':')) {
                [$filter, $argStr] = explode(':', $filter, 2);
                if (strlen($argStr) > 1 && str_contains($argStr, ',')) {
                    $args = Str::toTypedList($argStr);
                } else {
                    $args = [Str::toTyped($argStr)];
                }
            }

            switch ($filter) {
                case 'minlen':
                case 'minLen':
                    if (strlen($str) < (int)$args[0]) {
                        $str = '';
                    }
                    break;
                case 'maxlen':
                case 'maxLen':
                    if (strlen($str) > (int)$args[0]) {
                        $str = '';
                    }
                    break;
                case 'wrap':
                    $str = Str::wrap($str, ...$args);
                    break;
                case 'sub':
                case 'substr':
                    $str = substr($str, ...$args);
                    break;
                case 'append':
                    $str .= $argStr;
                    break;
                case 'prepend':
                    $str = $argStr . $str;
                    break;
                case 'replace':
                    $str = (string)str_replace($args[0], $args[1], $str);
                    break;
                default:
                    throw new InvalidArgumentException("unsupported filter: $filter");
            }
        }

        return $str;
    }

    /**
     * Filtering the input multi line text contents
     *
     * @arguments
     * text     The source text for handle.
     *
     *          Special:
     *          input '@c' or '@cb' or '@clipboard' - will read from Clipboard
     *          input empty or '@i' or '@stdin'     - will read from STDIN
     *          input '@l' or '@load'               - will read from loaded file
     *          input '@FILEPATH'                   - will read from the filepath
     *
     * @options
     *  -e, --exclude      array;sub text should not contains keywords.
     *  -i, --include      array;sub text should contains keywords.
     *      --no-trim      bool;trim the each sub text.
     *      --each         bool;Operate on each substr after split.
     *  -c, --cut          cut each line by the separator. cut position: L R, eg: 'L='
     *  -o, --output       write result to the output;;stdout
     *      --join-sep     join the each line by the separator
     *  -s, --sep          The separator char for split contents. defaults is newline(\n).
     *  -f, --filter       array;apply filter for handle each sub text.
     *                     allow filters:
     *                     - replace       replace substr
     *                     - notEmpty      filter empty line
     *                     - minlen        limit min length. `minlen:6`
     *                     - maxlen        limit max length. `maxlen:16`
     *                     - wrap          wrap each line. `wrap:'`
     *                     - sub           substr handle. `sub:0,2`
     *                     - prepend       prepend char each line. `prepend:-`
     *                     - append        append char to each line. `append:'`
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @example
     *  {binWithCmd} --cut 'L,'     # cut by ',' and remove left
     *  {binWithCmd} -f "replace:A,a" # replace A to a for each line
     *  {binWithCmd} -f "wrap:'"
     *
     */
    public function processCommand(FlagsParser $fs, Output $output): void
    {
        $text = $fs->getArg('text');
        $text = ContentsAutoReader::readFrom($text, [
            'loadedFile' => $this->dumpfile,
        ]);
        if (!$text) {
            $output->warning('empty source contents for handle');
            return;
        }

        $ex = $fs->getOpt('exclude');
        $in = $fs->getOpt('include');

        $trim = !$fs->getOpt('no-trim');
        $cut  = $fs->getOpt('cut');
        $sep  = $fs->getOpt('sep', "\n");
        // filters
        $filters = $fs->getOpt('filter');

        $cutPos  = 'L';
        $cutChar = $cut;
        if (strlen($cut) > 1) {
            if ($cut[0] === 'L') {
                // $cutPos = 'L';
                $cutChar = substr($cut, 1);
            } elseif ($cut[0] === 'R') {
                $cutPos  = 'R';
                $cutChar = substr($cut, 1);
            }
        }

        $s = StringStream::new(explode($sep, $text))
            ->mapIf('trim', $trim)
            ->mapIf(function (string $line) use ($cutPos, $cutChar) {
                if (!str_contains($line, $cutChar)) {
                    return $line;
                }

                [$left, $right] = explode($cutChar, $line, 2);
                return $cutPos === 'L' ? $right : $left;
            }, $cut)
            ->filterIf(function (string $line) use ($ex) { // exclude
                return !Str::has($line, $ex);
            }, count($ex) > 0)
            ->filterIf(function (string $line) use ($in) { // include
                return Str::has($line, $in);
            }, count($in) > 0)
            ->mapIf(function (string $line) use ($filters) { // filters
                return $this->applyFilters($line, $filters);
            }, $filters);

        $result  = $s->implode($sep);
        $outFile = $fs->getOpt('output');

        ContentsAutoWriter::writeTo($outFile, $result);
    }

    /**
     * Replace all occurrences of the search string with the replacement string
     *
     * @arguments
     * text     The source text for handle.
     *          Special:
     *          input '@c' or '@cb' or '@clipboard' - will read from Clipboard
     *          input empty or '@i' or '@stdin'     - will read from STDIN
     *          input '@l' or '@load'               - will read from loaded file
     *          input '@FILEPATH'                   - will read from the filepath
     *
     * @options
     *  -f, --from          array;The replace from chars
     *  -t, --to            array;The replace to chars
     *  --rm, --remove      array;Want remove some chars, allow multi
     *  --regex             bool; mark the from is regex expr string.
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @example
     *   {binWithCmd} -f '"' 'a "abc" "abc"' # Output: a abc abc
     */
    public function replaceCommand(FlagsParser $fs, Output $output): void
    {
        $text = trim($fs->getArg('text'));
        $text = ContentsAutoReader::readFrom($text);

        if ($rmList = $fs->getOpt('remove')) {
            $output->writeRaw(str_replace($rmList, '', $text));
            return;
        }

        $from = $fs->getOpt('from');
        $to   = $fs->getOpt('to');
        if (!$from && !$to) {
            $output->warning('the from and to cannot all empty');
            return;
        }

        if ($fs->getOpt('regex')) {
            $output->aList(array_combine($from, $to), 'regex map');
            foreach ($from as $i => $pattern) {
                $text = preg_replace($pattern, $to[$i] ?? '', $text);
            }

            $output->writeRaw($text);
            return;
        }

        $search = $replace = [];
        foreach ($from as $item) {
            $search[] = TextParser::resolveSep($item);
        }
        foreach ($to as $item) {
            $replace[] = TextParser::resolveSep($item);
        }

        $output->aList(array_combine($search, $replace), 'replace map');
        $output->writeRaw(str_replace($search, $replace, $text));
    }

    /**
     * parse and collect fields from multi line contents
     *
     * @arguments
     *  source     The source text for parse. allow: FILEPATH, @clipboard
     *
     * @options
     *       --fields               The field names, split by ','
     *       --get-cols             Only get the provide index cols, start is 0. eg: 1,5
     *   -o, --output               The output target. default is stdout.
     *  --of, --out-fmt             The output format. allow: raw, md/md-table, table, json
     *  --is, --item-sep            The item sep char. default is NL.
     *  --vn, --value-num           int;The item value number. default get from first line.
     *  --vs, --value-sep           The item value sep char for 'space' parser. default is SPACE
     *  --parser, --item-parser     The item parser name for difference data type. allow:
     *                              space       -  parser substr by space
     *                              json, json5 -  parser json(5) line
     *
     */
    public function parseCommand(FlagsParser $fs, Output $output): void
    {
        $text = $fs->getArg('source');
        $text = ContentsAutoReader::readFrom($text, [
            'loadedFile' => $this->dumpfile,
        ]);

        $p = TextParser::new($text);
        $p->setItemSep($fs->getOpt('item-sep'));
        $p->setFieldNum($fs->getOpt('value-num'));

        if ($valueSep = $fs->getOpt('value-sep')) {
            $p->setItemParser(TextParser::charSplitParser($valueSep));
        }

        $indexes   = [];
        $idxString = $fs->getOpt('get-cols');
        if ($idxString && !$indexes = Str::toInts($idxString)) {
            throw new InvalidArgumentException('please provide valid column index string.');
        }

        switch ($fs->getOpt('item-parser')) {
            case 'json':
            case 'json5':
                $itemParser = new Json5ItemParser;
                break;
            case 'space':
            default:
                $valueSep   = $fs->getOpt('value-sep', ' ');
                $itemParser = TextParser::charSplitParser($valueSep);
                // $itemParser = TextItemParser::new($valueSep, $indexes);
                break;
        }

        $p->setItemParser($itemParser);
        $p->setFields($fs->getOptStrAsArray('fields'));
        $p->parse();

        $result   = '';
        $doOutput = true;
        switch ($fs->getOpt('out-fmt')) {
            case 'md':
            case 'mdtable':
            case 'mdTable':
            case 'md-table':
                $rows = ListStream::new($p->getData())
                    ->eachToArray(function (array $item) {
                        return implode(' | ', $item);
                    });
                $head = array_shift($rows);
                $line = implode('|', array_pad(['-----'], $p->fieldNum, '-----'));

                $result = $head . "\n" . $line . "\n" . implode("\n", $rows);
                break;
            case 'raw':
            case 'text':
                $rows = $p->stream()->eachToArray(function (array $item) use ($indexes) {
                    return implode('  ', Arr::gets($item, $indexes));
                });

                $result = implode("\n", $rows);
                break;
            case 'table':
                Table::show($p->getData(true));
                $doOutput = false;
                break;
            default:
                $result = Json::pretty($p->getData(true));
                break;
        }

        if ($doOutput) {
            $outFile = $fs->getOpt('output');
            ContentsAutoWriter::writeTo($outFile, $result);
        }
    }

    /**
     * Change case for input string.
     *
     * @options
     *  -s, --source        string;The source code for convert. allow: string, @clipboard;true
     *  -o, --output        The output target. default is stdout.
     *      --case          The target case. allow: _,-, ,snake,camel,upper,lower
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function caseCommand(FlagsParser $fs, Output $output): void
    {
        $source = $fs->getOpt('source');
        $source = ContentsAutoReader::readFrom($source, []);

        if (!$source) {
            throw new InvalidArgumentException('empty source code for convert');
        }

        $output->info('TODO');
    }

    /**
     * Calc length for input string.
     *
     * @options
     *  --nt, --not-trim       bool;dont run trim for input
     *  -s, --sep              Split by the sep char, then count elements
     *
     * @arguments
     *  source        string;The source code for calc. allow: string, @clipboard;true
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function lengthCommand(FlagsParser $fs, Output $output): void
    {
        $source  = ContentsAutoReader::readFrom($fs->getArg('source'));
        $trimmed = trim($source);
        $output->println("INPUT: $source");

        if ($sep = $fs->getOpt('sep')) {
            $list  = Str::explode($trimmed, $sep);
            $count = count($list);
        } else {
            $count = strlen($trimmed);
        }

        $output->colored('Length: ' . $count);
    }
}
