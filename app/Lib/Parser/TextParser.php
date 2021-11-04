<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser;

use Closure;
use Toolkit\Stdlib\Str;
use function array_filter;
use function array_merge;
use function array_slice;
use function count;
use function explode;
use function implode;
use function trim;

/**
 * Class TextParser
 */
class TextParser
{
    public const PREPEND = 1;
    public const APPEND  = 2;

    /**
     * @var string
     */
    private string $text;

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
     * @var array
     */
    private array $rows = [];

    /**
     * @var string
     */
    public string $lineSep = "\n";

    /**
     * field number of each line.
     *
     * @var int
     */
    public int $fieldNum = 3;

    /**
     * @var string
     */
    public string $fieldSep = ' ';

    /**
     * @var array
     */
    public array $fields = [];

    /**
     * @var callable(string): string
     */
    private $filterFn;

    /**
     * handler for parse one line text
     *
     * @var callable(string): array
     * @example function (string $rawLine, int $rawIndex): array {}
     */
    private $parserFn;

    /**
     * on field values num < filedNum
     *
     * @var int
     */
    private int $valueLtFieldNum = self::PREPEND;

    public static function new(): self
    {
        return new self();
    }

    /**
     * @param callable $filterFn
     *
     * @return $this
     */
    public function withFilter(callable $filterFn): self
    {
        $this->filterFn = $filterFn;
        return $this;
    }

    /**
     * @param callable $parserFn
     *
     * @return $this
     */
    public function withParser(callable $parserFn): self
    {
        $this->parserFn = $parserFn;

        return $this;
    }

    /**
     * @return $this
     */
    public function parse(string $text): self
    {
        $this->text = $text;

        $text = trim($text, $this->lineSep);
        if (str_contains($text, $this->headerSep)) {
            [$header, $text] = explode($this->headerSep, $text);

            $this->parseHeader($header);
        }

        $rawLines = explode($this->lineSep, $text);

        $filterFn = $this->filterFn;
        $parserFn = $this->parserFn;
        $fieldNum = $this->fieldNum;

        $this->rows = [];
        foreach ($rawLines as $line) {
            $line = trim($line);
            // is comments line
            if (self::isCommentsLine($line)) {
                continue;
            }

            // is not contains fieldSep
            // if (!str_contains($line, $this->fieldSep)) {
            //     continue;
            // }

            // do filtering line text
            if ($filterFn && !($line = $filterFn($line))) {
                continue;
            }

            // custom parser func
            if ($parserFn) {
                $values = $parserFn($line, $fieldNum);
            } else { // default parser func
                $values = $this->applyDefaultParser($line);
            }

            $this->collectRow($fieldNum, $values);
        }

        return $this;
    }

    protected function parseHeader(string $header): void
    {
        foreach (explode("\n", $header) as $line) {
            if (!$line = trim($line)) {
                continue;
            }

            // not and k-v format OR is comments line
            if (!str_contains($line, '=') || self::isCommentsLine($line)) {
                continue;
            }

            [$key, $value] = Str::explode($line, '=');
            switch ($key) {
                case 'fieldNum':
                    $this->fieldNum = (int)$value;
                    break;
                case 'fields':
                    $this->fields = Str::explode($value, ',');
                    break;
            }
        }

        $allowSettings  = ['fieldNum', 'fields'];
        $this->settings = IniParser::parseString($header);

        foreach ($allowSettings as $prop) {
            if (isset($this->settings[$prop])) {
                $this->$prop = $this->settings[$prop];
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
    private function collectRow(int $fieldNum, array $values): void
    {
        $row = [];
        if (count($values) < $fieldNum) {
            if ($this->valueLtFieldNum === self::APPEND) {
                $row = $values;
            } else { // PREPEND
                $prevIdx = count($this->rows) - 1;
                if ($prevIdx < 0) { // 丢弃 discard
                    return;
                }

                $prevRow = $this->rows[$prevIdx];

                // append to prev row's last value.
                $prevRow[$fieldNum - 1] .= implode($this->fieldSep, $values);

                $this->rows[$prevIdx]   = $prevRow;
                return;
            }
        } else { // merge to last node
            $last = '';
            foreach ($values as $i => $value) {
                if ($i < $fieldNum) {
                    $row[] = $value;
                } else {
                    $last .= $value;
                }
            }
            $row[] = $last;
        }

        $this->rows[] = $row;
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
        foreach ($this->rows as $index => $row) {
            $handlerFn($row, $index);
        }
    }

    /**
     * @return Closure
     */
    public static function spaceSplitParser(): Closure
    {
        return static function (string $line, int $fieldNum) {
            $nodes = array_filter(explode(' ', $line), 'strlen');
            $count = count($nodes);
            if ($count <= $fieldNum) {
                return $nodes;
            }

            $values = array_slice($nodes, 0, $fieldNum - 1);
            $others = array_slice($nodes, $fieldNum - 1);

            // merge others as last elem
            $values[] = implode(' ', $others);
            return $values;
        };
    }

    /**
     * @param string $rawLine
     *
     * @return array
     */
    public function applyDefaultParser(string $rawLine): array
    {
        $values = explode($this->fieldSep, $rawLine, $this->fieldNum);

        return array_filter($values, 'strlen');
    }

    /**
     * @return array
     */
    public function getRows(): array
    {
        return $this->rows;
    }

    /**
     * @param int $valueLtFieldNum
     *
     * @return TextParser
     */
    public function setValueLtFieldNum(int $valueLtFieldNum): TextParser
    {
        $this->valueLtFieldNum = $valueLtFieldNum;
        return $this;
    }

    /**
     * @param callable $filterFn
     */
    public function setFilterFn(callable $filterFn): void
    {
        $this->filterFn = $filterFn;
    }

    /**
     * @param callable $parserFn
     */
    public function setParserFn(callable $parserFn): void
    {
        $this->parserFn = $parserFn;
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
}
