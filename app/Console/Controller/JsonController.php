<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Controller;

use ColinODell\Json5\Json5Decoder;
use Inhere\Console\Component\Formatter\JSONPretty;
use Inhere\Console\Controller;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\Clipboard;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Helper\KiteUtil;
use Inhere\Kite\Kite;
use Inhere\Kite\Lib\Parser\Text\Json5LineParser;
use Inhere\Kite\Lib\Parser\Text\TextParser;
use InvalidArgumentException;
use JsonException;
use Throwable;
use Toolkit\FsUtil\File;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Arr;
use Toolkit\Stdlib\Helper\JsonHelper;
use Toolkit\Stdlib\OS;
use function gettype;
use function is_file;
use function is_scalar;
use function json_decode;
use function str_contains;
use function trim;
use const JSON_THROW_ON_ERROR;

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
            'toText'  => ['2kv', 'to-kv', '2text'],
            'pretty'  => ['fmt', 'format'],
            'fields'  => ['field', 'comment', 'comments'],
            'toClass' => ['class', 'dto', 'todto', 'to-dto'],
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
     * @throws Throwable
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
     * @throws Throwable
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
     * @throws Throwable
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
     * @throws Throwable
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
     * @throws Throwable
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
     * @throws Throwable
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

        $parser = TextParser::emptyWithParser(new Json5LineParser());
        $fields = $parser
            ->parse($json)
            ->getStringMap('field', 'comment');

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
     * convert JSON(5) object string to PHP/JAVA DTO class.
     *
     * @arguments
     *  source     The source json contents
     *
     * @options
     *  -o, --output        The output target. default is STDOUT.
     *      --tpl-dir       The custom template file dir.
     *      --tpl-file      The custom template file path.
     *  -t, --type          string;the generate code language type, allow: java, php;;php
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @throws Throwable
     */
    public function toClassCommand(FlagsParser $fs, Output $output): void
    {
        $json = $fs->getArg('source');
        $json = ContentsAutoReader::readFrom($json, [
            'loadedFile' => $this->dumpfile,
        ]);

        if (!$json = trim($json)) {
            throw new InvalidArgumentException('empty input json(5) text for handle');
        }

        if ($json[0] !== '{') {
            $json = '{' . $json . "\n}";
        }

        $type = $fs->getOpt('type');
        $data = Json5Decoder::decode($json, true);

        $comments = [];
        if (str_contains($json, '//')) {
            $p = TextParser::newWithParser($json, new Json5LineParser())
                ->withConfig(function (TextParser $p) {
                    $p->headerSep = "\n//###\n";
                })
                ->parse();

            $comments = $p->getStringMap('field', 'comment');
            // $output->aList($comments);
        }

        $fields = [];
        foreach ($data as $key => $value) {
            $fields[$key] = [
                'name' => $key,
                'type' => gettype($value),
                'desc' => $comments[$key] ?? $key,
            ];
        }

        $output->aList($fields, 'field list');

        $tplFile = $fs->getOpt('tpl-file');
        if (!$tplFile) {
            $tplFile = "@kite-res-tpl/dto-class/$type-data-dto.tpl";
        }

        $tplFile = Kite::alias($tplFile);

        $tplBody = File::readAll($tplFile);
        $tplEng  = KiteUtil::newTplEngine($tplBody);
        // if ($type === 'php') {
        //     $output->info('HI');
        // } elseif ($type === 'java') {
        //
        // }

        $settings = [
            'user' => OS::getUserName(),
        ];
        $tplVars  = [
            'ctx'    => $settings,
            'fields' => $fields,
        ];
        $contents = $tplEng->apply($tplVars);

        $output->colored('------------------ Generated Codes -------------------');
        $output->writeRaw($contents);
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
