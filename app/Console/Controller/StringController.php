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
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Kite;
use InvalidArgumentException;
use Toolkit\FsUtil\File;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Str;
use function explode;
use function implode;
use function is_file;
use function trim;

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
            'join'   => ['implode', 'j'],
            'split'  => ['s'],
            'filter' => ['f'],
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
     *          input '@' or empty     - will read from Clipboard
     *          input '@i' or '@stdin' - will read from STDIN
     *          input '@l' or '@load'  - will read from loaded file
     *          input '@FILEPATH'      - will read from the filepath.
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
        $text = AppHelper::tryReadContents($text, $this->dumpfile);

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
     *          input '@' or empty     - will read from Clipboard
     *          input '@i' or '@stdin' - will read from STDIN
     *          input '@l' or '@load'  - will read from loaded file
     *          input '@FILEPATH'      - will read from the filepath.
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
        $text = AppHelper::tryReadContents($text);

        if (!$text) {
            $output->warning('empty input contents for handle');
            return;
        }

        $sep = $fs->getOpt('sep', ' ');

        $lines = explode($sep, $text);
        echo implode("\n", $lines), "\n";
    }

    /**
     * Filtering the input text contents
     *
     * @arguments
     * text     The source text for handle.
     *          Special:
     *          input '@c' or '@cb' or '@clipboard' - will read from Clipboard
     *          input empty or '@i' or '@stdin'     - will read from STDIN
     *          input '@l' or '@load'               - will read from loaded file
     *          input '@FILEPATH'                   - will read from the filepath.
     *
     * @options
     *  -e, --exclude   array;exclude lines on contains keywords.
     *  -m, --match     array;include lines on contains keywords.
     *  -t, --trim      trim the input contents.
     *  -s, --sep       The separator char. defaults is newline(\n).
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function filterCommand(FlagsParser $fs, Output $output): void
    {
        $text = $fs->getArg('text');
        $text = AppHelper::tryReadContents($text, $this->dumpfile, ['print' => false]);
        if (!$text) {
            $output->warning('empty source contents for handle');
            return;
        }

        $ex  = $fs->getOpt('exclude');
        $in  = $fs->getOpt('match');
        $sep = $fs->getOpt('sep', "\n");

        $lines = explode($sep, $text);

        $filtered = [];
        foreach ($lines as $line) {
            if ($in) {
                if (Str::has($line, $in)) {
                    $filtered[] = $line;
                }
                continue;
            }

            if ($ex && Str::has($line, $ex)) {
                continue;
            }
            $filtered[] = $line;
        }

        echo implode($sep, $filtered), "\n";
    }
}
