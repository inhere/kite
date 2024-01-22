<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser;

use Inhere\Kite\Lib\Defines\ClassMeta;
use Inhere\Kite\Lib\Defines\FieldMeta;

/**
 * (go/php/java) DTO class parser
 *
 * @author inhere
 */
abstract class AbstractDTOParser
{
    /**
     * @var string source code
     */
    protected string $content = '';

    /**
     * @var array all tokens in source
     */
    protected array $tokens = [];

    /**
     * @return ClassMeta or subclass
     */
    abstract public function doParse(): ClassMeta;

    abstract protected function parseClassLine(string $line, ClassMeta $meta): void;

    abstract protected function parseFieldLine(string $line, ?FieldMeta $field): FieldMeta;

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
