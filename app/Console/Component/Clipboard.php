<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Component;

use Toolkit\FsUtil\File;
use Toolkit\Stdlib\Obj\AbstractObj;
use Toolkit\Stdlib\OS;
use Toolkit\Sys\Exec;
use function addslashes;
use function strpos;
use function tempnam;

/**
 * Class Clipboard
 *
 * @package Inhere\Kite\Helper
 */
class Clipboard extends AbstractObj
{
    public const WRITER_ON_MAC   = 'pbcopy';
    public const WRITER_ON_WIN   = 'clip';
    public const WRITER_ON_LINUX = 'xsel';

    public const READER_ON_MAC   = 'pbpaste';
    public const READER_ON_WIN   = 'clip';
    public const READER_ON_LINUX = 'xclip';

    /**
     * @var string
     */
    private $writerApp;

    /**
     * @var string
     */
    private $readerApp;

    public function __construct()
    {
        parent::__construct();

        $this->writerApp = $this->getWriterByOS();
        $this->readerApp = $this->getReaderByOS();
    }

    /**
     * @param string $contents
     * @param bool $addSlashes
     *
     * @return bool
     */
    public function write(string $contents, bool $addSlashes = false): bool
    {
        $program = $this->writerApp;
        if (!$program) {
            return false;
        }

        // $contents = trim($contents);
        if ($addSlashes) {
            $contents = addslashes($contents);
        }

        // $contents = str_replace("\n", " \\\n", $contents);
        $multiLine = strpos($contents, "\n") !== false;

        // linux:
        //   # Copy input to clipboard
        // 	 echo -n "$input" | xclip -selection c
        // Mac:
        //  echo hello | pbcopy
        //  pbcopy < tempfile.txt
        if ($multiLine) {
            $file = tempnam(OS::tempDir(), "tmp_");

            File::write($contents, $file);
            $command = "$program < $file";
        } else {
            $command = "echo $contents | $program";
        }

        $result = Exec::auto($command);
        return (int)$result['status'] === 0;
    }

    /**
     * @return string
     */
    public function read(): string
    {
        $program = $this->readerApp;
        if (!$program) {
            return '';
        }

        $result = Exec::auto($program);

        return $result['output'];
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    public function readToFile(string $file): bool
    {
        $program = $this->readerApp;
        if (!$program) {
            return false;
        }

        // Mac: pbpaste >> tasklist.txt
        $result = Exec::auto("$program >> $file");

        return (int)$result['status'] === 0;
    }

    /**
     * @return string
     */
    protected function getWriterByOS(): string
    {
        if (OS::isWindows()) {
            return self::WRITER_ON_WIN;
        }

        if (OS::isMac()) {
            return self::WRITER_ON_MAC;
        }

        if (OS::isLinux()) {
            return self::WRITER_ON_LINUX;
        }

        return '';
    }

    /**
     * @return string
     */
    protected function getReaderByOS(): string
    {
        if (OS::isWindows()) {
            return self::READER_ON_WIN;
        }

        if (OS::isMac()) {
            return self::READER_ON_MAC;
        }

        if (OS::isLinux()) {
            return self::READER_ON_LINUX;
        }

        return '';
    }

    /**
     * @return string
     */
    public function getReaderApp(): string
    {
        return $this->readerApp;
    }

    /**
     * @return string
     */
    public function getWriterApp(): string
    {
        return $this->writerApp;
    }
}
