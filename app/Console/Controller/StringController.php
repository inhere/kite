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
use Inhere\Kite\Console\Component\Clipboard;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Kite;
use Inhere\Kite\Lib\Stream\StringsStream;
use InvalidArgumentException;
use Toolkit\FsUtil\File;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Str;
use function count;
use function explode;
use function implode;
use function is_file;
use function preg_match;
use function str_contains;
use function str_replace;
use function strlen;
use function substr;
use function trim;
use function vdump;

/**
 * Class StringController
 */
class StringController extends Controller
{
    protected static $name = 'string';

    protected static $description = 'Some useful development tool commands';

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
        ];
    }

    protected function init(): void
    {
        parent::init();

        $this->dumpfile = Kite::getTmpPath('string-loaded.txt');
    }

    /**
     * @throws JsonException
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
     * @param Output $output
     */
    public function joinCommand(FlagsParser $fs, Output $output): void
    {
        $text = trim($fs->getArg('text'));
        $text = AppHelper::tryReadContents($text, [
            'loadedFile' => $this->dumpfile,
        ]);

        if (!$text) {
            $output->warning('empty input contents for handle');
            return;
        }

        $lines = explode("\n", $text);
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
     *  -s, --sep    The separator char. defaults is an space string.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function splitCommand(FlagsParser $fs, Output $output): void
    {
        $text = trim($fs->getArg('text'));
        $text = ContentsAutoReader::readFrom($text);

        if (!$text) {
            $output->warning('empty input contents for handle');
            return;
        }

        $sep = $fs->getOpt('sep', ' ');

        $lines = explode($sep, $text);
        echo implode("\n", $lines), "\n";
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
     *  -f, --from    The replace from char
     *  -t, --to      The replace to chars
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function replaceCommand(FlagsParser $fs, Output $output): void
    {
        $text = trim($fs->getArg('text'));
        $text = ContentsAutoReader::readFrom($text);
        if (!$text) {
            $output->warning('empty input contents for handle');
            return;
        }

        $from = $fs->getOpt('from');
        $to   = $fs->getOpt('to');
        if (!$from && !$to) {
            $output->warning('the from and to cannot all empty');
            return;
        }

        $output->writeRaw(str_replace($from, $to, $text));
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
     *  -e, --exclude   array;exclude line on contains keywords.
     *  -m, --match     array;include line on contains keywords.
     *  -t, --trim      bool;trim the each line text.
     *      --wrap      wrap the each line by the separator
     *  -j, --join      join the each line by the separator
     *  -c, --cut       cut each line by the separator. cut position: L R, eg: 'L='
     *  -r, --replace   array;replace chars for each line
     *  -s, --sep       The separator char for split contents. defaults is newline(\n).
     *  -f, --filter    array;apply more filter for handle text.
     *                  allow filters:
     *                  - notEmpty      filter empty line
     *                  - min           limit min length
     *                  - wrap          wrap each line. wrap:'
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @example
     *  {binWithCmd} --cut 'L,' # cut by = and remove left
     *  {binWithCmd} --replace 'A/a' # replace A to a for each line
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
        $in = $fs->getOpt('match');

        $trim = $fs->getOpt('trim');
        $cut  = $fs->getOpt('cut');
        $sep  = $fs->getOpt('sep', "\n");
        //
        $replaces = $fs->getOpt('replace');

        $cutPos = 'L';
        $cutChar = $cut;
        if (strlen($cut) > 1) {
            if ($cut[0] === 'L') {
                $cutPos = 'L';
                $cutChar= substr($cut, 1);
            } elseif ($cut[0] === 'R') {
                $cutPos = 'R';
                $cutChar= substr($cut, 1);
            }
        }

        $s = StringsStream::new(explode($sep, $text))
            ->eachIf('trim', $trim)
            ->eachIf(function (string $line) use ($cutPos, $cutChar) {
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
            ->eachIf(function (string $line) use ($replaces) { // replace
                $froms = $tos = [];
                foreach ($replaces as $replace) {
                    [$from, $to] = explode('/', $replace, 2);
                    $froms[] = $from;
                    $tos[]   = $to;
                }

                // vdump($line);
                return str_replace($froms, $tos, $line);
            }, $replaces);

        echo $s->implode($sep), "\n";
    }

    /**
     * collect field and comments from multi line contents
     *
     * @arguments
     * text     The source text contents.
     *
     */
    public function fieldsCommand(FlagsParser $fs, Output $output): void
    {
        $text = $fs->getArg('text');
        $text = ContentsAutoReader::readFrom($text, [
            'loadedFile' => $this->dumpfile,
        ]);

        if (!$text) {
            throw new InvalidArgumentException('please input text for handle');
        }

        $fields = [];
        foreach (explode("\n", $text) as $line) {
            if (!str_contains($line, '//')) {
                continue;
            }

            [$jsonLine, $comments] = Str::explode($line, '//', 2);
            if (!preg_match('/[a-zA-Z][\w_]+/', $jsonLine, $matches)) {
                continue;
            }

            // vdump($matches);
            $fields[$matches[0]] = $comments;
        }

        $output->aList($fields);
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
        $source = AppHelper::tryReadContents($source);

        if (!$source) {
            throw new InvalidArgumentException('empty source code for convert');
        }

        $output->info('TODO');
    }
}
