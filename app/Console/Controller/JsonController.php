<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Controller;

use Inhere\Console\Component\Formatter\JSONPretty;
use Inhere\Console\Controller;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\Clipboard;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Kite;
use InvalidArgumentException;
use JsonException;
use Toolkit\FsUtil\File;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Arr;
use Toolkit\Stdlib\Helper\JsonHelper;
use Toolkit\Stdlib\Str;
use function explode;
use function is_file;
use function is_scalar;
use function json_decode;
use function preg_match;
use function preg_replace;
use function str_contains;
use function trim;
use function vdump;

/**
 * Class DemoController
 */
class JsonController extends Controller
{
    protected static $name = 'json';

    protected static $description = 'Some useful json development tool commands';

    /**
     * @var string
     */
    private string $dumpfile = '';

    private string $json;

    /**
     * @var array
     */
    private array $data = [];

    protected static function commandAliases(): array
    {
        return [
            'toText' => ['2kv', 'to-kv', '2text'],
            'pretty' => ['fmt', 'format'],
        ];
    }

    protected function init(): void
    {
        parent::init();

        $this->dumpfile = Kite::getPath('tmp/json-load.json');
    }

    protected function jsonRender(): JSONPretty
    {
        return JSONPretty::new([
            // 'theme' => JSONPretty::THEME_ONE
        ]);
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

        $this->json = File::readAll($dumpfile);
        $this->data = json_decode($this->json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param string $source
     *
     * @throws JsonException
     */
    private function autoReadJSON(string $source): void
    {
        $this->json = ContentsAutoReader::readFrom($source, [
            'loadedFile' => $this->dumpfile,
        ]);
        if (!$this->json) {
            throw new InvalidArgumentException('the source json data is empty');
        }

        $this->data = json_decode($this->json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * load json string data from clipboard to an tmp file
     *
     * @arguments
     * source   The source. allow: @clipboard, @stdin
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @throws JsonException
     */
    public function loadCommand(FlagsParser $fs, Output $output): void
    {
        $json = AppHelper::tryReadContents($fs->getArg('source'));
        if (!$json) {
            throw new InvalidArgumentException('the input data is empty');
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
     *     --type        The search type. allow: keys, path
     * -s, --source      The json data source, default read stdin, allow: @load, @clipboard, @stdin
     *
     * @throws JsonException
     */
    public function getCommand(FlagsParser $fs, Output $output): void
    {
        $source = $fs->getOpt('source');
        $this->autoReadJSON($source);

        $path = $fs->getArg('path');
        $ret  = Arr::getByPath($this->data, $path);

        if (is_scalar($ret)) {
            $output->println($ret);
        } else {
            // $output->prettyJSON($ret);
            $output->write($this->jsonRender()->renderData($ret));
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
            if (str_contains($key, $kw)) {
                $ret[$key] = $val;
            }
        }

        if (is_scalar($ret)) {
            $output->println($ret);
        } else {
            // $output->prettyJSON($ret);
            $output->write($this->jsonRender()->renderData($ret));
        }
    }

    /**
     * pretty and format JSON text.
     *
     * @arguments
     * json     The json text line. if empty will try read text from clipboard
     *
     * @throws JsonException
     */
    public function prettyCommand(FlagsParser $fs, Output $output): void
    {
        $json = $fs->getArg('json');
        $json = AppHelper::tryReadContents($json, [
            'loadedFile' => $this->dumpfile,
        ]);

        if (!$json) {
            throw new InvalidArgumentException('please input json text for pretty');
        }

        // $data = json_decode($json, true);
        // $output->prettyJSON($data);
        // $output->colored('PRETTY JSON:');
        $output->write($this->jsonRender()->render($json));
    }

    /**
     * collect field and comments from JSON5 contents
     *
     * @arguments
     * json5     The json text line. if empty will try read text from clipboard
     *
     */
    public function fieldsCommand(FlagsParser $fs, Output $output): void
    {
        $json = $fs->getArg('json5');
        $json = AppHelper::tryReadContents($json, [
            'loadedFile' => $this->dumpfile,
        ]);

        if (!$json) {
            throw new InvalidArgumentException('please input json(5) text for handle');
        }

        $fields = [];
        foreach (explode("\n", $json) as $line) {
            if (!str_contains($line, ':') || !str_contains($line, '//')) {
                continue;
            }

            // is comments line
            $trimmed = trim($line);
            if (str_starts_with($trimmed, '#') || str_starts_with($trimmed, '//')) {
                continue;
            }

            if (str_contains($trimmed, '://')) {
                $trimmed = preg_replace('/https?:\/\//', 'XX', $trimmed);
                if (!str_contains($trimmed, '//')) {
                    continue;
                }
            }

            [$jsonLine, $comments] = Str::explode($trimmed, '//', 2);
            if (!preg_match('/[a-zA-Z][\w_]+/', $jsonLine, $matches)) {
                continue;
            }

            // vdump($matches);
            $fields[$matches[0]] = $comments;
        }

        $output->aList($fields);
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
     * convert JSON object string to PHP/JAVA DTO class.
     *
     * @options
     *  -s, --source     The source json contents
     *  -o, --output     The output target. default is STDOUT.
     *  -t, --type       string;the generate code language type, allow: java, php;;php
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @throws JsonException
     */
    public function toClassCommand(FlagsParser $fs, Output $output): void
    {
        $json = $fs->getArg('json');
        $json = ContentsAutoReader::readFrom($json, [
            'loadedFile' => $this->dumpfile,
        ]);

        if (!$json = trim($json)) {
            throw new InvalidArgumentException('empty input json(5) text for handle');
        }

        if ($json[0] !== '{') {
            $json = '{' . $json . '}';
        }

        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);


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
