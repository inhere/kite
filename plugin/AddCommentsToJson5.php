<?php declare(strict_types=1);

use Inhere\Console\Application;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use Inhere\Kite\Console\Component\ContentsAutoWriter;
use Inhere\Kite\Console\Plugin\AbstractPlugin;
use Inhere\Kite\Kite;
use Inhere\Kite\Lib\Parser\Text\TextParser;
use Inhere\Kite\Lib\Stream\ListStream;
use Toolkit\FsUtil\File;
use Toolkit\Stdlib\Str;

/**
 * class AddCommentsToJson5
 */
class AddCommentsToJson5 extends AbstractPlugin
{
    protected function metadata(): array
    {
        return [
            'desc'    => 'add field comments for text(eg: json5) contents',
            'example' => 'kite plug AddComments2Json -- -h'
        ];
    }

    protected function options(): array
    {
        return [
            's,source'    => [
                'desc'     => 'the source json contents, allow: FILEPATH, @stdin, @clipboard',
                'helpType' => 'SOURCE',
            ],
            'l, list'     => 'bool;list the field comments data map',
            'm, map-file' => 'the field comments data map file;true',
            // 'w, map-file' => 'the field comments data map file;true',
            'c, case'     => 'want change field case',
            'output,o'    => 'write formatted result to output, default is STDOUT.
if equals @source will write to the source FILEPATH'
        ];
    }

    /**
     * @var array
     */
    private array $exclude = [];

    /**
     * @var array
     */
    private array $mapData = [];

    /**
     * @param Application $app
     * @param Output $output
     */
    public function exec(Application $app, Output $output): void
    {
        $mapFile = $this->fs->getOpt('map-file');
        $this->loadMapData($mapFile);

        // vdump($this->mapData);
        $app->colored('Loaded fields count: ' . count($this->mapData));
        if ($this->fs->getOpt('list')) {
            $app->getOutput()->aList($this->mapData, 'Fields');
            return;
        }

        $parent = '';
        $source = $this->fs->getOpt('source');

        $reader  = ContentsAutoReader::new();
        $srcText = $reader->read($source);

        $fmtLines = [];
        foreach (explode("\n", $srcText) as $line) {
            $trimmed = trim($line);
            // empty or exists comments
            if (!$trimmed) {
                $fmtLines[] = $line;
                continue;
            }

            // 匹配字段名
            if (preg_match('/^\s*[\'"]?([a-zA-Z][\w_]+)/', $trimmed, $matches)) {
                $field = $matches[1];
                $fullK = $parent ? $parent . '.'. $field : '';

                // 有注释标记了。检查特殊标记
                if (strpos($trimmed, '//') > 0) {
                    [$prefix, $comment] = Str::explode($trimmed, '//', 2);

                    // 字段映射到map的别名 用于一些通用字段
                    // 比如 type, status 等; type -> order.type, goods.type
                    if ($comment && str_ends_with($comment, ".$field")) {
                        $line  = $prefix;
                        $field = $comment;
                    } else {
                        $fmtLines[] = $line;
                        // start mark: eg 'object: {', 'array: ['
                        if (Str::isEndWiths($prefix, ['{', '['])) {
                            $parent = $field;
                        }
                        continue;
                    }

                    // start mark: eg 'object: {', 'array: ['
                } elseif (Str::isEndWiths($trimmed, ['{', '['])) {
                    $parent = $field;
                }

                // add comments
                if ($fullK && isset($this->mapData[$fullK])) {
                    $line .= ' // ' . $this->mapData[$fullK];
                } elseif (isset($this->mapData[$field]) && !in_array($field, $this->exclude, true)) {
                    $line .= ' // ' . $this->mapData[$field];
                }

                // object,array end: '}', ']'
            } elseif (Str::isEndWiths(rtrim($trimmed, ','), ['}', ']'])) {
                $parent = '';
            }

            $fmtLines[] = $line;
        }

        $result = implode("\n", $fmtLines);
        $outFile = $this->fs->getOpt('output');

        ContentsAutoWriter::writeTo($outFile, $result);
    }

    protected function loadMapData(string $mapFile): void
    {
        $mapText  = File::readAll(Kite::alias($mapFile));

        $p = TextParser::new($mapText);
        $p->parse();

        $this->mapData = ListStream::new($p->getData())
            ->filter(function (array $item) {
                return count($item) >= 2;
            })
            ->eachToMap(function (array $item) {
                $field = $item[0];
                $field = str_contains($field, '_') ? Str::toCamelCase($field) : $field;

                $desc = $item[2] ?? $item[1];
                return [$field, $desc];
            });
    }
}
