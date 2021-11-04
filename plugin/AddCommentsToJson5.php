<?php declare(strict_types=1);

use Inhere\Console\Application;
use Inhere\Console\IO\Input;
use Inhere\Kite\Console\Component\Clipboard;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use Inhere\Kite\Console\Plugin\AbstractPlugin;
use Inhere\Kite\Kite;
use Inhere\Kite\Lib\Parser\IniParser;
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
     * @param Input $input
     */
    public function exec(Application $app, Input $input): void
    {
        $mapFile = $this->fs->getOpt('map-file');
        $this->loadMapData($mapFile);

        // vdump($this->mapData);
        $app->colored('Loaded fields count: ' . count($this->mapData));
        if ($this->fs->getOpt('list')) {
            $app->getOutput()->aList($this->mapData, 'Fields');
            return;
        }

        $source = $this->fs->getOpt('source');

        $reader  = ContentsAutoReader::new();
        $srcText = $reader->read($source);

        $fmtLines = [];
        foreach (explode("\n", $srcText) as $line) {
            $trimmed = trim($line);
            if (!$trimmed || str_contains($trimmed, '//')) {
                $fmtLines[] = $line;
                continue;
            }

            if (preg_match('/[a-zA-Z][\w_]+/', $trimmed, $matches)) {
                // vdump($matches);
                $field = $matches[0];

                // add comments
                if (isset($this->mapData[$field]) && !in_array($field, $this->exclude, true)) {
                    $line .= ' // ' . $this->mapData[$field];
                }
            }

            $fmtLines[] = $line;
        }

        $result = implode("\n", $fmtLines);
        $output = $this->fs->getOpt('output');

        if ($output === '@c' || $output === '@cb' || $output === '@clipboard') {
            Clipboard::writeString($result);
        } else {
            $app->getOutput()->writeRaw($result);
        }
    }

    protected function loadMapData(string $mapFile): void
    {
        $header = '';
        $mapText  = File::readAll(Kite::alias($mapFile));

        if (str_contains($mapText, "\n###")) {
            [$header, $mapText] = explode("\n###", $mapText);
        } else {
            $mapText = trim($mapText);
        }

        if ($header) {
            $settings = IniParser::parseString($header);
            if (isset($settings['exclude'])) {
                $this->exclude = (array)$settings['exclude'];
            }
        }

        // load fields
        foreach (explode("\n", $mapText) as $line) {
            if (!$line = trim($line)) {
                continue;
            }

            if (!str_contains($line, ' ')) {
                continue;
            }

            // is comments line
            if (str_starts_with($line, '#') || str_starts_with($line, '//')) {
                continue;
            }

            [$field, $desc] = Str::explode($line, ' ', 2);
            if ($field && $desc) {
                $field = str_contains($field, '_') ? Str::toCamelCase($field) : $field;
                // add
                $this->mapData[$field] = $desc;
            }
        }
    }
}
