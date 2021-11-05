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
use Inhere\Kite\Lib\Generate\JsonToCode;
use Inhere\Kite\Lib\Parser\Text\Json5LineParser;
use Inhere\Kite\Lib\Parser\Text\TextParser;
use InvalidArgumentException;
use Throwable;
use Toolkit\FsUtil\File;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Arr;
use Toolkit\Stdlib\Helper\JsonHelper;
use function array_filter;
use function array_merge;
use function is_file;
use function is_scalar;
use function json_decode;
use function str_contains;
use function trim;
use function vdump;
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

        $parser = TextParser::newWithParser($json, new Json5LineParser());
        $fields = $parser->getStringMap('field', 'comment');

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
        $config = Kite::cliApp()->getArrayParam('json_toClass');

        $type = $fs->getOpt('type');
        $json = $fs->getArg('source');
        $json = ContentsAutoReader::readFrom($json, [
            'loadedFile' => $this->dumpfile,
        ]);

        if (!$json = trim($json)) {
            throw new InvalidArgumentException('empty input json(5) text for handle');
        }

        $tplDir  = $fs->getOpt('tpl-dir');
        $tplFile = $fs->getOpt('tpl-file');
        if (!$tplFile) {
            // $tplFile = "@resource-tpl/dto-class/$type-data-dto.tpl";
            $tplFile = "@user-custom/template/$type-code-tpl/req-resp-dto.tpl";
        }

        $config = array_merge($config, array_filter([
            'tplDir'  => $tplDir,
            'tplFile' => $tplFile,
        ]));
vdump($config);
        // @user-custom/template/java-service-tpl/req-resp-dto.tpl
        $gen = JsonToCode::create($type)
            ->setSource($json)
            ->configObj($config)
            ->setPathResolver([Kite::class, 'alias'])
            ->prepare();

        $output->aList($gen->getFields(), 'field list');

        $contents = $gen->generate();

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
