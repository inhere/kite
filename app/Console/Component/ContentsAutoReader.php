<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Component;

use Inhere\Kite\Helper\KiteUtil;
use Inhere\Kite\Kite;
use InvalidArgumentException;
use Toolkit\Cli\Cli;
use Toolkit\FsUtil\File;
use Toolkit\Stdlib\Obj\AbstractObj;
use function fclose;
use function fopen;
use function is_file;
use function stream_get_contents;
use function stream_set_blocking;
use function substr;
use function vdump;

/**
 * class ContentsAutoReader
 */
class ContentsAutoReader extends AbstractObj
{
    public const TYPE_CLIPBOARD = 'clipboard';

    public const TYPE_FILE   = 'file';
    public const TYPE_STDIN  = 'stdin';
    public const TYPE_STRING = 'string';

    /**
     * @var string
     */
    protected string $srcType = self::TYPE_STRING;

    /**
     * @param string $source
     * @param array $opts
     *
     * @return string
     */
    public static function readFrom(string $source, array $opts = []): string
    {
        return (new self())->read($source, $opts);
    }

    /**
     * try read contents
     *
     * - input empty or '@i' or '@stdin'     - will read from STDIN
     * - input '@c' or '@cb' or '@clipboard' - will read from Clipboard
     * - input '@l' or '@load'               - will read from loaded file
     * - input '@FILEPATH' or FILEPATH       - will read from the filepath.
     *
     * @param string $source the input text
     * @param array{print: bool, loadedFile: string, throwOnEmpty: true} $opts
     *
     * @return string
     */
    public function read(string $source, array $opts = []): string
    {
        $print = $opts['print'] ?? false;
        $lFile = $opts['loadedFile'] ?? '';

        $str = $source;
        if (!$source) {
            $this->srcType = self::TYPE_STDIN;
            $print && Cli::info('try read contents from STDIN');
            $str = Kite::cliApp()->getInput()->readAll();

            // is one line text
        } elseif (!str_contains($source, "\n")) {
            if (KiteUtil::isStdinAlias($source)) {
                $this->srcType = self::TYPE_STDIN;
                $print && Cli::info('try read contents from STDIN');
                $str = Kite::cliApp()->getInput()->readAll();
                // $str = File::streamReadAll(STDIN);
                // $str = File::readAll('php://stdin');
                // vdump($str);
                // Cli::info('try read contents from STDOUT'); // error
                // $str = Kite::cliApp()->getOutput()->readAll();
            } elseif (KiteUtil::isClipboardAlias($source)) {
                $this->srcType = self::TYPE_CLIPBOARD;
                $print && Cli::info('try read contents from Clipboard');
                $str = Clipboard::new()->read();
            } elseif (($source === '@l' || $source === '@load') && ($lFile && is_file($lFile))) {
                $this->srcType = self::TYPE_FILE;
                $print && Cli::info('try read contents from file: ' . $lFile);
                $str = File::readAll($lFile);
            } else {
                $filepath = Kite::alias($source);
                if ($filepath[0] === '@') {
                    $filepath = substr($filepath, 1);
                }

                if (is_file($filepath)) {
                    $this->srcType = self::TYPE_FILE;
                    $print && Cli::info('try read contents from file: ' . $filepath);
                    $str = File::readAll($filepath);
                }
            }
        }

        if (($opts['throwOnEmpty'] ?? true) && !$str) {
            throw new InvalidArgumentException('Nothing contents was read');
        }

        return $str;
    }

    /**
     * @return string
     */
    protected function readFromStdin(): string
    {
        $text  = '';
        $stdin = fopen('php://stdin', 'r');

        if (stream_set_blocking($stdin, false)) {
            $text = stream_get_contents($stdin);
        }

        fclose($stdin);
        return $text;
    }

    /**
     * @return string
     */
    public function getSrcType(): string
    {
        return $this->srcType;
    }
}
