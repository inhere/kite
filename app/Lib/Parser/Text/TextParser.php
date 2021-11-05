<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser\Text;

use Closure;
use Inhere\Kite\Lib\Parser\IniParser;
use InvalidArgumentException;
use function array_combine;
use function array_filter;
use function array_merge;
use function array_pad;
use function array_slice;
use function array_values;
use function count;
use function explode;
use function implode;
use function trim;

/**
 * class TextParser
 */
class TextParser
{
    // add to prev row's last value.
    public const PREPEND = 1;
    public const APPEND  = 2;
    public const DISCARD = 3;

    /**
     * @var string
     */
    private string $text;

    /**
     * @var string
     */
    private string $textHeader = '';

    /**
     * @var string
     */
    private string $textBody = '';

    /**
     * @var bool
     */
    private bool $prepared = false;

    /**
     * the custom settings
     * - parsed from text header
     *
     * @var array
     */
    private array $settings = [];

    /**
     * split the settings header and body
     *
     * @var string
     */
    public string $headerSep = "\n###\n";

    /**
     * @var bool
     */
    public bool $parseHeader = true;

    /**
     * @var callable(string): string
     */
    private $beforeParseHeader;

    /**
     * @var string
     */
    public string $lineSep = "\n";

    /**
     * @var array
     */
    public array $fields = [];

    /**
     * field number of each line.
     *
     * @var int
     */
    public int $fieldNum = 0;

    /**
     * how to handle error item
     * - on item values number < filedNum
     *
     * @var int
     */
    public int $errItemHandleType = self::DISCARD;

    /**
     * collected data list
     *
     * @var array[]
     */
    private array $data = [];

    /**
     * @var callable(string): string
     */
    private $lineFilter;

    /**
     * parse one line to data item
     * - default line parser {@see spaceSplitParser}
     *
     * @var callable(string): array
     */
    private $lineParser;

    /**
     * @param string $text
     *
     * @return self
     */
    public static function new(string $text = ''): self
    {
        return new self($text);
    }

    /**
     * @return static
     */
    public static function parseText(string $text): self
    {
        return (new self($text))->parse();
    }

    /**
     * @param callable(string):array $lineParser
     *
     * @return static
     */
    public static function emptyWithParser(callable $lineParser): self
    {
        return (new self())->withParser($lineParser);
    }

    /**
     * @param callable(string):array $lineParser
     *
     * @return static
     */
    public static function newWithParser(string $text, callable $lineParser): self
    {
        return (new self($text))->withParser($lineParser);
    }

    /**
     * Class constructor.
     *
     * @param string $text
     */
    public function __construct(string $text = '')
    {
        $this->text = $text;
    }

    /**
     * @param string $text
     *
     * @return TextParser
     */
    public function withText(string $text): self
    {
        if ($text) {
            $this->text = $text;
        }
        return $this;
    }

    /**
     * @param Closure $fn
     *
     * @return TextParser
     */
    public function withConfig(Closure $fn): self
    {
        $fn($this);
        return $this;
    }

    /**
     * @param callable $lineFilter
     *
     * @return $this
     */
    public function withFilter(callable $lineFilter): self
    {
        $this->lineFilter = $lineFilter;
        return $this;
    }

    /**
     * @param callable(string):array $lineParser
     *
     * @return $this
     */
    public function withParser(callable $lineParser): self
    {
        $this->lineParser = $lineParser;
        return $this;
    }

    /**
     * prepare
     *  - split header, body
     *  - parse header settings
     *
     * @return $this
     */
    public function prepare(): self
    {
        if ($this->prepared) {
            return $this;
        }

        $this->prepared = true;

        $text = trim($this->text, $this->lineSep);
        if (str_contains($text, $this->headerSep)) {
            [$header, $text] = explode($this->headerSep, $text);

            $this->textHeader = $header;
            $this->parseHeaderSettings($header);
        }

        $this->textBody = $text;
        return $this;
    }

    /**
     * @return $this
     */
    public function parse(string $text = ''): self
    {
        if (!$this->prepared) {
            $this->setText($text);
        }

        // prepare
        $this->prepare();

        $lfFn = $this->lineFilter;
        $lpFn = $this->lineParser ?: self::spaceSplitParser();

        $this->data = [];
        $fieldNum   = $this->fieldNum;

        foreach (explode($this->lineSep, $this->textBody) as $line) {
            // empty line
            if (!$line = trim($line)) {
                continue;
            }

            // is comments line
            if (self::isCommentsLine($line)) {
                continue;
            }

            // do filtering line text
            if ($lfFn && !($line = $lfFn($line))) {
                continue;
            }

            // parser line to item
            $values = $lpFn($line, $fieldNum);

            // invalid item
            if (!$values) {
                continue;
            }

            // up: not set the field number, use first item values count.
            if ($fieldNum === 0) {
                $this->fieldNum = $fieldNum = count($values);
            }

            $this->collectDataItem($fieldNum, $values);
        }

        return $this;
    }

    /**
     * @param string $header
     */
    protected function parseHeaderSettings(string $header): void
    {
        if ($beforeFn = $this->beforeParseHeader) {
            $header = $beforeFn($header);
        }

        $this->settings = IniParser::parseString($header);

        $allowConfig = ['fieldNum', 'fields'];
        foreach ($allowConfig as $prop) {
            if (!isset($this->settings[$prop])) {
                continue;
            }

            $value = $this->settings[$prop];
            switch ($prop) {
                case 'fieldNum':
                    $this->fieldNum = (int)$value;
                    break;
                case 'fields':
                    $this->fields = (array)$value;
                    break;
            }
        }

        if ($this->fields) {
            $this->fieldNum = count($this->fields);
        }
    }

    /**
     * @param int   $fieldNum
     * @param array $values
     */
    private function collectDataItem(int $fieldNum, array $values): void
    {
        $row = [];
        $num = count($values);

        if ($num === $fieldNum) {
            $row = $values;
        } elseif ($num < $fieldNum) {
            // 丢弃 discard
            if ($this->errItemHandleType === self::DISCARD) {
                return;
            }

            // APPEND
            if ($this->errItemHandleType === self::APPEND) {
                $row = $values;
            } else { // PREPEND
                $prevIdx = count($this->data) - 1;
                if ($prevIdx < 0) { // 丢弃 discard
                    return;
                }

                $lastIdx = $fieldNum - 1;
                $prevRow = $this->data[$prevIdx];
                if (!isset($prevRow[$lastIdx])) {
                    return;
                }

                // append to prev item's last value.
                $prevRow[$lastIdx] .= ' ' . implode(' ', $values);
                $this->data[$prevIdx] = $prevRow;
                return;
            }
        } else { // merge to last node
            $lIdx = 0;
            $last = [];
            foreach ($values as $i => $value) {
                if ($i < $fieldNum) {
                    $row[$i] = $value;
                } else {
                    if ($lIdx === 0) {
                        $lIdx = $i;
                    }
                    $last[] = $value;
                }
            }

            $row[$lIdx] = implode(' ', $last);
        }

        $this->data[] = $row;
    }

    /**
     * @param string $line
     *
     * @return bool
     */
    public static function isCommentsLine(string $line): bool
    {
        return str_starts_with($line, '#') || str_starts_with($line, '//');
    }

    /**
     * @param callable $handlerFn
     */
    public function each(callable $handlerFn): void
    {
        foreach ($this->data as $index => $row) {
            $handlerFn($row, $index);
        }
    }

    /**
     * this is default line parser
     *
     * @return Closure
     */
    public static function spaceSplitParser(): Closure
    {
        return static function (string $line, int $fieldNum) {
            $nodes = array_filter(explode(' ', $line), 'strlen');
            $count = count($nodes);
            if ($count <= $fieldNum) {
                return array_values($nodes);
            }

            $values = array_slice($nodes, 0, $fieldNum - 1);
            $others = array_slice($nodes, $fieldNum - 1);

            // merge others as last elem
            $values[] = implode(' ', $others);
            return array_values($values);
        };
    }

    /**
     * @param bool $indexToField  replace item value index to field name, require the {@see $fields}
     *
     * @return array[]
     */
    public function getData(bool $indexToField = false): array
    {
        if ($indexToField && $this->fields) {
            $new = [];
            foreach ($this->data as $item) {
                // on item elem number < $this->fieldNum, pad empty string value.
                if (count($item) < $this->fieldNum) {
                    $item = array_pad($item, $this->fieldNum, '');
                }

                $new[] = array_combine($this->fields, $item);
            }

            return $new;
        }

        return $this->data;
    }

    /**
     * @param int|string $keyIdxOrName
     * @param bool $indexToField  replace item value index to field name, require the {@see $fields}
     *
     * @return array<string, array>
     */
    public function getDataMap(int|string $keyIdxOrName = 0, bool $indexToField = false): array
    {
        $map = [];

        $indexToField = $indexToField && $this->fields;
        foreach ($this->data as $item) {
            if (!isset($item[$keyIdxOrName])) {
                throw new InvalidArgumentException("the data item index/key '$keyIdxOrName' not exists");
            }

            $mapKey = $item[$keyIdxOrName];
            if ($indexToField) {
                // on item elem number < $this->fieldNum, pad empty string value.
                if (count($item) < $this->fieldNum) {
                    $item = array_pad($item, $this->fieldNum, '');
                }

                $map[$mapKey] = array_combine($this->fields, $item);
                continue;
            }

            $map[$mapKey] = $item;
        }

        return $map;
    }

    /**
     * @param int|string $keyIdxOrName
     * @param int|string $valIdxOrName
     *
     * @return array<string, string>
     */
    public function getStringMap(int|string $keyIdxOrName, int|string $valIdxOrName): array
    {
        $map = [];
        foreach ($this->data as $item) {
            if (!isset($item[$keyIdxOrName], $item[$valIdxOrName])) {
                throw new InvalidArgumentException("the data item index/key '$keyIdxOrName' not exists");
            }

            $map[$item[$keyIdxOrName]] = $item[$valIdxOrName];
        }

        return $map;
    }

    /**
     * @return self
     */
    public function prependOnErrItem(): self
    {
        return $this->setErrItemHandleType(self::PREPEND);
    }

    /**
     * @return self
     */
    public function appendOnErrItem(): self
    {
        return $this->setErrItemHandleType(self::APPEND);
    }

    /**
     * @return self
     */
    public function discardOnErrItem(): self
    {
        return $this->setErrItemHandleType(self::DISCARD);
    }

    /**
     * @param int $errItemHandleType
     *
     * @return TextParser
     */
    public function setErrItemHandleType(int $errItemHandleType): self
    {
        $this->errItemHandleType = $errItemHandleType;
        return $this;
    }

    /**
     * @param callable $beforeParseHeader
     *
     * @return self
     */
    public function setBeforeParseHeader(callable $beforeParseHeader): self
    {
        $this->beforeParseHeader = $beforeParseHeader;
        return $this;
    }

    /**
     * @param callable $lineFilter
     */
    public function setLineFilter(callable $lineFilter): void
    {
        $this->lineFilter = $lineFilter;
    }

    /**
     * @param callable $lineParser
     */
    public function setLineParser(callable $lineParser): void
    {
        $this->lineParser = $lineParser;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     */
    public function setSettings(array $settings): void
    {
        $this->settings = array_merge($this->settings, $settings);
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        if ($text) {
            $this->text = $text;
        }
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param bool $parseHeader
     */
    public function setParseHeader(bool $parseHeader): void
    {
        $this->parseHeader = $parseHeader;
    }

    /**
     * @return string
     */
    public function getTextHeader(): string
    {
        return $this->textHeader;
    }

    /**
     * @return string
     */
    public function getTextBody(): string
    {
        return $this->textBody;
    }
}
