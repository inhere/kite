<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser\Java;

use Toolkit\Stdlib\Helper\Assert;
use Toolkit\Stdlib\Str;
use function file_get_contents;
use function preg_match;
use function rtrim;

/**
 * @author inhere
 */
class JavaDTOParser extends JavaDTOMeta
{
    /**
     * @var string source code
     */
    private string $content = '';

    private array $tokens = [];

    /**
     * Quick parse from source string
     *
     * @param string $content
     *
     * @return static
     */
    public static function parse(string $content): static
    {
        $obj = new static();
        $obj->setContent($content);
        return $obj->do();
    }

    /**
     * Quick parse from source file
     *
     * @param string $filePath
     *
     * @return static
     */
    public static function parseFromFile(string $filePath): static
    {
        $content = file_get_contents($filePath);
        return self::parse($content);
    }

    public const POS_HEADER = 0;
    public const POS_CLASS  = 1;
    public const POS_BODY   = 2;

    /**
     * Do parse the source code
     *
     * @return $this
     */
    public function do(): static
    {
        $src = trim($this->content);
        Assert::notEmpty($src, 'The content can not be empty');

        $field = null;
        $pos   = self::POS_HEADER;

        // split to array by \n
        $inComment = false;
        foreach (Str::splitTrimmed($src, "\n") as $line) {
            if (empty($line)) {
                continue;
            }

            if ($inComment) {
                if ($pos < self::POS_BODY) {
                    $this->addComment($line);
                } else {
                    $field->addComment($line);
                }

                if (Str::hasSuffix($line, '*/')) {
                    $inComment = false;
                }
                continue;
            }

            if (Str::startWith($line, 'package ')) {
                $this->package = substr($line, 8, -1);
                continue;
            }

            if (Str::startWith($line, 'import ')) {
                $pos = self::POS_CLASS;

                $this->imports[] = substr($line, 7, -1);
                continue;
            }

            // comments start
            if (Str::startWith($line, '/**')) {
                $inComment = true;
                if ($pos < self::POS_BODY) {
                    $pos = self::POS_CLASS;
                    $this->addComment($line);
                } else { // in body
                    if ($field) {
                        $this->fields[] = $field;
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
                    $this->annotations[] = substr($line, 1);
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
                $this->parseClassLine($line);
                continue;
            }

            // field line: protected|private|public
            if (Str::pregMatch('#^p[a-zA-Z]{5,} .*;$#', $line)) {
                $field = $this->parseFieldLine($line, $field);
            }
        }

        if ($field) {
            $this->fields[] = $field;
        }

        return $this;
    }

    private function parseClassLine(string $line): void
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
                $this->interfaces[] = $node;
                continue;
            }

            // is access modifier
            if ($this->isAccessModifier($node)) {
                $this->accessModifier = $node;
            } elseif ($this->isClassType($node)) {
                $this->type = $node;
                $this->name = $nodes[$i + 1]; // class name
                $skipNext   = true;
            } elseif ($node === 'extends') {
                $this->extends = $nodes[$i + 1];
                $skipNext      = true;
            } elseif ($node === 'implements') {
                $isIFace = true;
            } else { // final|static
                $this->otherModifiers[] = $node;
            }
        }
    }

    private function parseFieldLine(string $line, ?JavaField $field): JavaField
    {
        if (!$field) {
            $field = new JavaField();
        }

        $nodes = Str::splitTrimmed($line, ' ');
        foreach ($nodes as $i => $node) {
            if ($this->isAccessModifier($node)) {
                $field->accessModifier = $node;
            } elseif ($this->isOtherModifier($node)) {
                $field->otherModifiers[] = $node;
            } else {
                $field->type = $node;
                $field->name = rtrim($nodes[$i + 1], ' ;');
                break;
            }
        }

        return $field;
    }

    /**
     * @param string $s
     *
     * @return bool
     */
    private function isClassType(string $s): bool
    {
        return preg_match('#^(class|interface|enum)$#', $s) === 1;
    }

    /**
     * @param string $s
     *
     * @return bool
     */
    private function isAccessModifier(string $s): bool
    {
        return preg_match('#^(public|private|protected)$#i', $s) === 1;
    }

    private function isOtherModifier(string $s): bool
    {
        return preg_match('#^(final|static)$#i', $s) === 1;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }


}
