<?php declare(strict_types=1);

namespace Inhere\Kite\Common;

use function array_filter;
use function count;
use function explode;
use function implode;
use function trim;

/**
 * Class TextParser
 *
 * @package Inhere\Kite\Common
 */
class TextParser
{
    public const PREPEND = 1;
    public const APPEND  = 2;

    /**
     * @var string
     */
    private $text;

    /**
     * @var array
     */
    private $rows = [];

    /**
     * @var callable
     */
    private $filterFn;

    /**
     * handler for parse one line text
     *
     * @var callable
     * @example function (string $rawLine, int $rawIndex): array {}
     */
    private $parserFn;

    /**
     * @var string
     */
    private $rowChar = "\n";

    /**
     * @var int
     */
    private $fieldNum = 3;

    /**
     * @var string
     */
    private $valueChar = ' ';

    /**
     * @var int
     */
    private $valueLtFieldNum = self::PREPEND;

    public static function new(string $text): self
    {
        return new self($text);
    }

    /**
     * Class constructor.
     *
     * @param string $text
     */
    public function __construct(string $text)
    {
        $this->text = $text;
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
    public function parse(): self
    {
        $rawLines = explode($this->rowChar, trim($this->text, $this->rowChar));

        $filterFn = $this->filterFn;
        $parserFn = $this->parserFn;
        $fieldNum = $this->fieldNum;

        $this->rows = [];
        foreach ($rawLines as $index => $line) {
            // do filtering line text
            if ($filterFn && !($line = $filterFn($line))) {
                continue;
            }

            // custom parser func
            if ($parserFn) {
                $values = $parserFn($line, $index);
            } else { // default parser func
                $values = $this->applyParser($line);
            }

            $this->collectRow($fieldNum, $values);
        }

        return $this;
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
                $prevRow[$fieldNum - 1] .= implode('', $values);
                $this->rows[$prevIdx]   = $prevRow;
                return;
            }
        } else {
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
     * @param callable $handlerFn
     */
    public function each(callable $handlerFn): void
    {
        foreach ($this->rows as $index => $row) {
            $handlerFn($row, $index);
        }
    }

    /**
     * @param string $rawLine
     *
     * @return array
     */
    public function applyParser(string $rawLine): array
    {
        $values = explode($this->valueChar, $rawLine);

        return array_filter($values);
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
}
