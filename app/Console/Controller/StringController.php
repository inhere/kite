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
use Inhere\Kite\Console\Component\Clipboard;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Kite;
use InvalidArgumentException;
use Toolkit\FsUtil\File;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Helper\JsonHelper;
use function explode;
use function implode;
use function is_file;
use function json_decode;
use function str_contains;
use function str_starts_with;
use function substr;
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
    private string $dumpfile;

    public static function aliases(): array
    {
        return ['str', 'text'];
    }

    protected static function commandAliases(): array
    {
        return [
            'join' => ['implode'],
        ];
    }

    protected function init(): void
    {
        parent::init();

        $this->dumpfile = Kite::getTmpPath('string-loaded.json');
    }

    /**
     * load string data from clipboard to an tmp file
     *
     * @param Output $output
     *
     * @throws JsonException
     */
    public function loadCommand(Output $output): void
    {
        $str = Clipboard::new()->read();
        if (!$str) {
            throw new InvalidArgumentException('the clipboard data is empty');
        }

        File::write(JsonHelper::prettyJSON($data), $this->dumpfile);

        $output->success('Complete');
    }

    /**
     * Join multi line text
     *
     * @arguments
     * text     The source text for handle.
     *          input '@' or empty     - will read from Clipboard
     *          input '@i' or '@stdin' - will read from STDIN
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
        $text = AppHelper::tryReadContents($text);

        if (!$text) {
            $output->warning('empty input contents for handle');
            return;
        }

        $lines = explode("\n", $text);
        $sep = $fs->getOpt('sep');

        echo implode($sep, $lines), "\n";
    }

    /**
     * Split text to multi line
     *
     * @arguments
     * text     The source text for handle.
     *          input '@' or empty     - will read from Clipboard
     *          input '@i' or '@stdin' - will read from STDIN
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
     *          input '@' or empty     - will read from Clipboard
     *          input '@i' or '@stdin' - will read from STDIN
     *          input '@FILEPATH'      - will read from the filepath.
     *
     * @options
     *  -e, --exclude   exclude lines on contains keywords.
     *  -m, --match     include lines on contains keywords.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function filterCommand(FlagsParser $fs, Output $output): void
    {
        $text = trim($fs->getArg('text'));
        $text = AppHelper::tryReadContents($text, $this->dumpfile);

        if (!$text) {
            $output->warning('empty input contents for handle');
            return;
        }

        $sep = $fs->getOpt('sep', ' ');

        $lines = explode($sep, $text);

        echo implode("\n", $lines), "\n";
    }
}
