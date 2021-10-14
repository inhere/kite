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
use Inhere\Kite\Kite;
use InvalidArgumentException;
use JsonException;
use Toolkit\FsUtil\File;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Arr;
use Toolkit\Stdlib\Helper\JsonHelper;
use function is_file;
use function is_scalar;
use function json_decode;
use function strpos;

/**
 * Class DemoController
 */
class JsonController extends Controller
{
    protected static $name = 'json';

    protected static $description = 'Some useful json development tool commands';

    protected static function commandAliases(): array
    {
        return [
            'toText' => ['2kv', 'to-kv', '2text'],
            'pretty' => ['fmt', 'format'],
        ];
    }

    /**
     * @var string
     */
    private $dumpfile = '';

    /**
     * @var array
     */
    private $data = [];

    protected function init(): void
    {
        parent::init();

        $this->dumpfile = Kite::getPath('tmp/json-load.json');
    }

    /**
     * @throws JsonException
     */
    private function loadDumpfileJSON(): void
    {
        $dumpfile = $this->dumpfile;
        if (!$dumpfile || !is_file($dumpfile)) {
            throw new InvalidArgumentException("the json temp file '$dumpfile' is not exists");
        }

        $this->data = json_decode(File::readAll($dumpfile), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * load json string data from clipboard to an tmp file
     *
     * @param Output $output
     *
     * @throws JsonException
     */
    public function loadCommand(Output $output): void
    {
        $json = Clipboard::new()->read();
        if (!$json) {
            throw new InvalidArgumentException('the clipboard data is empty');
        }

        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        File::write(JsonHelper::prettyJSON($data), $this->dumpfile);

        $output->success('Complete');
    }

    /**
     * get data by path in the loaded JSON data.
     *
     * @arguments
     * path     string;The key path for search get;required
     *
     * @options
     * --type       The search type. allow: keys, path
     *
     * @throws JsonException
     */
    public function getCommand(FlagsParser $fs, Output $output): void
    {
        $this->loadDumpfileJSON();
        $path = $fs->getArg('path');

        $ret = Arr::getByPath($this->data, $path);

        if (is_scalar($ret)) {
            $output->println($ret);
        } else {
            $output->prettyJSON($ret);
        }
    }

    /**
     * search keywords in the loaded JSON data.
     *
     * @arguments
     *  keywords     The keywords for search
     *
     * @options
     *  --type       The search type position, default: key. allow: key, value, both
     * @throws JsonException
     */
    public function searchCommand(FlagsParser $fs, Output $output): void
    {
        $this->loadDumpfileJSON();

        $ret = [];
        $kw  = $fs->getArg('keywords');
        foreach ($this->data as $key => $val) {
            if (strpos($key, $kw) !== false) {
                $ret[$key] = $val;
            }
        }

        if (is_scalar($ret)) {
            $output->println($ret);
        } else {
            $output->prettyJSON($ret);
        }
    }

    /**
     * pretty and format JSON text.
     *
     * @arguments
     * json     The json text line. if empty will try read text from clipboard
     */
    public function prettyCommand(FlagsParser $fs, Output $output): void
    {
        $json = $fs->getArg('json');
        if (!$json) {
            $json = Clipboard::new()->read();

            if (!$json) {
                throw new InvalidArgumentException('please input json text for pretty');
            }
        }

        $data = json_decode($json, true);
        $output->prettyJSON($data);
    }

    /**
     * multi line JSON logs.
     */
    public function mlLogCommand(): void
    {
        $cb = Clipboard::new();

        $json = $cb->read();
        if (!$json) {
            throw new InvalidArgumentException('');
        }
    }

    /**
     * JSON to k-v text string.
     */
    public function ml2lineCommand(): void
    {
        $cb = Clipboard::new();

        $json = $cb->read();
        if (!$json) {
            throw new InvalidArgumentException('');
        }
    }

    /**
     * convert JSON object string to PHP class.
     *
     * @options
     *  --cb            bool;read input from clipboard
     *  -f,--file       The source markdown code
     *  -o,--output     The output target. default is stdout.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function toClassCommand(FlagsParser $fs, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * JSON to k-v text string.
     */
    public function toTextCommand(): void
    {
        $cb = Clipboard::new();

        $json = $cb->read();
        if (!$json) {
            throw new InvalidArgumentException('');
        }
    }
}
