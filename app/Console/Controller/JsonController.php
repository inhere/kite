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
use Inhere\Kite\Console\Component\ContentsAutoWriter;
use Inhere\Kite\Kite;
use Inhere\Kite\Lib\Generate\JsonToCode;
use Inhere\Kite\Lib\Parser\Text\Json5ItemParser;
use Inhere\Kite\Lib\Parser\Text\TextParser;
use InvalidArgumentException;
use Throwable;
use Toolkit\FsUtil\File;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Arr;
use Toolkit\Stdlib\Helper\JsonHelper;
use Toolkit\Stdlib\Json;
use function array_filter;
use function array_keys;
use function array_merge;
use function is_file;
use function is_scalar;
use function is_string;
use function json_decode;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function stripslashes;
use function trim;
use const JSON_THROW_ON_ERROR;

/**
 * Class DemoController
 */
class JsonController extends Controller
{
    protected static string $name = 'json';

    protected static string $desc = 'Some useful json development tool commands';

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

        $this->dumpfile = Kite::getTmpPath('json-load.json');
    }

    /**
     * @param int $index
     *
     * @return string
     */
    private function getDumpfile(int $index = 0): string
    {
        if ($index > 0) {
            return Kite::getTmpPath("json-load$index.json");
        }

        return Kite::getTmpPath("json-load.json");
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
        $dumpfile = $this->getDumpfile();
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
            'loadedFile' => $this->getDumpfile(),
            'suffix'     => '.json',
        ]);
        if (!$this->json) {
            throw new InvalidArgumentException('the source json data is empty');
        }

        $this->data = json_decode($this->json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * load json string data from clipboard to an tmp file
     *
     * @options
     * -i, --index  int;set the loaded tmp file index number;false;0
     *
     * @arguments
     * source   The source. allow: @clipboard, @stdin
     * output   The output file, default is @tmp/json-load.json
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @throws Throwable
     * @example
     * {binWithCmd} @c l1                   # will save to @tmp/l1.json
     * {binName} json load -s @tmp/l1 $
     */
    public function loadCommand(FlagsParser $fs, Output $output): void
    {
        $ext = '.json';
        $json = ContentsAutoReader::readFrom($fs->getArg('source'), [
            'suffix' => $ext,
        ]);
        if (!$json) {
            throw new InvalidArgumentException('the input data is empty');
        }

        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if ($file = $fs->getArg('output')) {
            $file = Kite::getTmpPath($file);
            $file = File::appendSuffix($file, $ext);
        } else {
            $file = $this->dumpfile;
        }

        $output->info("load temp JSON data to file: $file");
        File::write(JsonHelper::prettyJSON($data), $file);

        $output->success('Complete');
    }

    /**
     * get data by path in the loaded JSON data.
     *
     * @options
     *     --type               The search type. allow: keys, path
     * --nc, --no-color         bool;dont render color for output result.
     * -s, --source             The json data source, default read stdin, allow: @load, @clipboard, @stdin
     * -o, --output             The output, default is stdout, allow: @load, @clipboard, @stdout
     * -k, --keys               bool;only output all top key names for data.
     * -f, --filter             array;include filter by input keywords, like use grep.
     * -e, --exclude            array;exclude filter by input keywords.
     * -m, --match, --search    array;search and high line by input keywords.
     *
     * @arguments
     * path     string;The key path for search get, use `$` get all;required
     *
     * @throws Throwable
     * @example
     * {binWithCmd} -s @l --nc -e ' 0,' -e '[]' $   # exclude contains ' 0,' '[]' lines.
     *
     *   ## load to custom file and read
     *   {binName} json load @c l1           # will save to @tmp/l1.json
     *   {binWithCmd} -s @tmp/l1  $    # read and render from @tmp/l1.json
     */
    public function getCommand(FlagsParser $fs, Output $output): void
    {
        $source = $fs->getOpt('source');
        // read and decode JSON
        $this->autoReadJSON($source);

        $path = $fs->getArg('path');
        if ($path = trim($path, ' .$')) {
            $ret = Arr::getByPath($this->data, $path);
        } else {
            $ret = $this->data;
        }

        // is json string
        if (is_string($ret) && str_starts_with($ret, '{"')) {
            $output->info("find '$path' value is JSON string, auto decode");
            $ret = Json::decode($ret, true);
        }

        if ($ret === null) {
            $str = 'NULL';
        } elseif (is_scalar($ret)) {
            $str = (string)$ret;
        } else {
            if ($fs->getOpt('keys')) {
                $ret = array_keys($ret);
            }

            $render = $this->jsonRender()
                ->setNoColor($fs->getOpt('no-color'))
                ->setIncludes($fs->getOpt('filter'))
                ->setExcludes($fs->getOpt('exclude'))
                ->setMatches($fs->getOpt('search'));

            $str = $render->renderData($ret);
        }

        $outFile = $fs->getOpt('output');
        ContentsAutoWriter::writeTo($outFile, $str);
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
     * @options
     * --uq, --unquote      bool;unquote input string before format.
     *
     * @arguments
     * json     The json text line.  allow: @load, @clipboard, @stdin
     *          if empty will try read text from clipboard
     *
     * @throws Throwable
     */
    public function prettyCommand(FlagsParser $fs, Output $output): void
    {
        $json = $fs->getArg('json');
        $json = ContentsAutoReader::readFrom($json, [
            'loadedFile' => $this->dumpfile,
        ]);

        if ($fs->getOpt('unquote')) {
            $json = stripslashes($json);
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
     * json5     The json text line.  allow: @load, @clipboard, @stdin
     *           if empty will try read text from clipboard
     *
     */
    public function fieldsCommand(FlagsParser $fs, Output $output): void
    {
        $json = $fs->getArg('json5');
        $json = ContentsAutoReader::readFrom($json, [
            'loadedFile' => $this->dumpfile,
        ]);

        if (!$json) {
            throw new InvalidArgumentException('please input json(5) text for handle');
        }

        $parser = TextParser::newWithParser($json, new Json5ItemParser());
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
     *  source     The source json contents, allow use @
     *
     * @options
     *  -o, --output               The output target. default is STDOUT.
     *      --tpl-dir              The custom template file dir path.
     *      --tpl, --tpl-file      The custom template file name or path.
     *  -t, --type                 string;the generate code language type, allow: java, php;;php
     *  -c, --ctx                  array;provide context data, allow multi, format KEY:VALUE
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @throws Throwable
     */
    public function toClassCommand(FlagsParser $fs, Output $output): void
    {
        $type = $fs->getOpt('type');
        $json = $fs->getArg('source');
        $json = ContentsAutoReader::readFrom($json, [
            'loadedFile' => $this->dumpfile,
        ]);

        if (!$json = trim($json)) {
            throw new InvalidArgumentException('empty input json(5) text for handle');
        }

        $config = Kite::config()->getArray('json_toClass');
        $tplDir = $fs->getOpt('tpl-dir', $config['tplDir'] ?? '');
        $tplDir = str_replace('{type}', $type, $tplDir);

        $tplFile = $fs->getOpt('tpl-file');
        if (!$tplFile) {
            $tplFile = "$tplDir/dto.tpl";
        }

        $config = array_merge($config, array_filter([
            'tplDir'  => $tplDir,
            'tplFile' => $tplFile,
        ]));

        $output->aList($config);

        // @user-custom/template/java-service-tpl/dto.tpl
        $gen = JsonToCode::create($type)
            ->setSource($json)
            ->configThis($config)
            ->loadVarsFromStrings($fs->getOpt('ctx'))
            ->setPathResolver([Kite::class, 'resolve'])
            ->prepare();

        $output->aList($gen->getContexts(), 'Tpl Contexts');
        $output->aList($gen->getFields(), 'field list');

        $result = $gen->generate();
        $output->colored('------------------ Generated Codes -------------------');
        $output->writeRaw($result);
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
