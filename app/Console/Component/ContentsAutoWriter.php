<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Component;

use Inhere\Kite\Helper\KiteUtil;
use Inhere\Kite\Kite;
use Toolkit\FsUtil\File;
use function in_array;

/**
 * class ContentsAutoWriter
 */
class ContentsAutoWriter
{
    /**
     * @var bool
     */
    private bool $printTips = true;

    // private  $tipsPrinter;

    /**
     * @var string
     */
    private string $output = '';

    /**
     * @param string $output
     * @param string $contents
     *
     * @return bool
     */
    public static function writeTo(string $output, string $contents): bool
    {
        return (new self)->setOutput($output)->write($contents);
    }

    /**
     * Class constructor.
     *
     * @param string $output
     */
    public function __construct(string $output = '')
    {
        $this->output = $output;
    }

    /**
     * @param string $contents
     *
     * @return bool
     */
    public function write(string $contents): bool
    {
        $ok = true;
        $out = $this->output;

        if (KiteUtil::isClipboardAlias($out)) {
            if ($this->printTips) {
                Kite::cliApp()->getOutput()->info('results has been sent to the Clipboard');
            }

            $ok = Clipboard::writeString($contents);
        } elseif (!$out || KiteUtil::isStdoutAlias($out)) {
            if ($this->printTips) {
                Kite::cliApp()->getOutput()->colored('RESULT:');
            }

            Kite::cliApp()->getOutput()->writeRaw($contents);
        } else {
            // write to file
            if ($this->printTips) {
                Kite::cliApp()->getOutput()->info('write results to file: ' . $this->output);
            }

            $filepath = Kite::resolve($this->output);

            // write
            File::mkdirSave($contents, $filepath);
        }

        return $ok;
    }

    /**
     * @return bool
     */
    public function IsToStdout(): bool
    {
        return !$this->output || in_array($this->output, ['@o', '@stdout'], true);
    }

    /**
     * @param string $output
     *
     * @return ContentsAutoWriter
     */
    public function setOutput(string $output): self
    {
        $this->output = $output;
        return $this;
    }
}
