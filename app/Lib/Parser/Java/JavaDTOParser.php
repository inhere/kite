<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser\Java;

use Inhere\Kite\Lib\Defines\ClassMeta;
use Inhere\Kite\Lib\Defines\FieldMeta;
use Inhere\Kite\Lib\Parser\AbstractDTOParser;
use Inhere\Kite\Lib\Parser\DTOParser;
use Toolkit\Stdlib\Helper\Assert;
use Toolkit\Stdlib\Obj;
use Toolkit\Stdlib\Str;
use function rtrim;

/**
 * @author inhere
 */
class JavaDTOParser extends AbstractDTOParser
{
    /**
     * Quick parse from source string
     *
     * @param string $content
     * @param array  $options for parser.
     *
     * @return JavaDTOMeta
     */
    public static function parse(string $content, array $options = []): JavaDTOMeta
    {
        $obj = new static();
        $obj->setContent($content);
        Obj::init($obj, $options);
        return $obj->doParse();
    }

    /** @var int contain: package, imports statement */
    public const POS_HEADER = 0;

    /** @var int contain: class comment and class define */
    public const POS_CLASS = 1;

    /** @var int contain: fields and methods */
    public const POS_BODY = 2;

    private int $startPos = self::POS_HEADER;

    /**
     * Do parse the source code
     *
     * @return JavaDTOMeta
     */
    public function doParse(): JavaDTOMeta
    {
        $pos = $this->startPos;
        $src = trim($this->content);
        Assert::notEmpty($src, 'The content can not be empty');

        $field = null;
        $meta  = new JavaDTOMeta();

        // split to array by \n
        $inComment = false;
        foreach (Str::splitTrimmed($src, "\n") as $line) {
            if (empty($line)) {
                continue;
            }

            if ($inComment) {
                if ($pos < self::POS_BODY) {
                    $meta->addComment($line);
                } else {
                    $field->addComment($line);
                }

                if (Str::hasSuffix($line, '*/')) {
                    $inComment = false;
                }
                continue;
            }

            if (Str::startWith($line, 'package ')) {
                $meta->package = substr($line, 8, -1);
                continue;
            }

            if (Str::startWith($line, 'import ')) {
                $pos = self::POS_CLASS;

                $meta->imports[] = substr($line, 7, -1);
                continue;
            }

            // comments start
            if (Str::startWith($line, '/**')) {
                $inComment = true;
                if ($pos < self::POS_BODY) {
                    $pos = self::POS_CLASS;
                    $meta->addComment($line);
                } else { // in body
                    if ($field) {
                        $meta->fields[] = $field;
                    }

                    // start new field
                    $field = new JavaField();
                    $field->addComment($line);
                }

                continue;
            }

            // annotations
            if (Str::startWith($line, '@')) {
                if ($pos < self::POS_BODY) {
                    $meta->annotations[] = substr($line, 1);
                } else {
                    if (!$field) {
                        $field = new JavaField();
                    }
                    $field->annotations[] = substr($line, 1);
                }
                continue;
            }

            // class or interface or enum
            if ($pos === self::POS_CLASS && Str::contains($line, [' class ', ' interface ', ' enum '])) {
                $pos = self::POS_BODY;
                $this->parseClassLine($line, $meta);
                continue;
            }

            // field line: protected|private|public
            if (Str::pregMatch('#^p[a-zA-Z]{5,} .*;$#', $line)) {
                $field = $this->parseFieldLine($line, $field);
            }
        }

        if ($field) {
            $meta->fields[] = $field;
        }

        return $meta;
    }

    protected function parseClassLine(string $line, ClassMeta $meta): void
    {
        $nodes = Str::splitTrimmed($line, ' ');

        $skipNext = $isIFace = false;
        foreach ($nodes as $i => $node) {
            if ($skipNext) {
                $skipNext = false;
                continue;
            }
            if ($node === '{') {
                continue;
            }

            if ($isIFace) {
                $meta->interfaces[] = $node;
                continue;
            }

            // is access modifier
            if (DTOParser::isAccessModifier($node)) {
                $meta->accessModifier = $node;
            } elseif (DTOParser::isClassType($node)) {
                $meta->type = $node;
                $meta->name = $nodes[$i + 1]; // class name
                $skipNext   = true;
            } elseif ($node === 'extends') {
                $meta->extends = $nodes[$i + 1];
                $skipNext      = true;
            } elseif ($node === 'implements') {
                $isIFace = true;
            } else { // final|static
                $meta->otherModifiers[] = $node;
            }
        }
    }

    protected function parseFieldLine(string $line, ?FieldMeta $field): FieldMeta
    {
        if (!$field) {
            $field = new JavaField();
        }

        $nodes = Str::splitTrimmed($line, ' ');
        foreach ($nodes as $i => $node) {
            if (DTOParser::isAccessModifier($node)) {
                $field->accessModifier = $node;
            } elseif (DTOParser::isOtherModifier($node)) {
                $field->otherModifiers[] = $node;
            } else {
                $field->setType($node);
                $field->name = rtrim($nodes[$i + 1], ' ;');
                break;
            }
        }

        return $field;
    }

    public function setStartPos(int $startPos): void
    {
        $this->startPos = $startPos;
    }

}
