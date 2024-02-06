<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Defines;

use Toolkit\Stdlib\Obj\BaseObject;
use Toolkit\Stdlib\Str\StrBuilder;

/**
 * @author inhere
 */
class ClassMeta extends BaseObject
{
    /**
     * @var string package path for java,go. namespace for php
     */
    public string $package = '';

    /**
     * @var array imports classes for java. use classes for php
     */
    public array $imports = [];

    /**
     * @var string access modifier
     */
    public string $accessModifier = AccessModifier::DEFAULT;

    /**
     * @var string[] other modifiers: final|static|abstract
     */
    public array $otherModifiers = [];

    /**
     * @var string class type
     */
    public string $type = ElementType::TYPE_CLASS;

    /**
     * @var string class name
     */
    public string $name = '';

    /**
     * @var string class description
     */
    public string $description = '';

    /**
     * @var string[] class comment
     */
    public array $comments = [];

    /**
     * @var array annotations on class
     */
    public array $annotations = [];

    /**
     * @var string extends class
     */
    public string $extends = '';

    /**
     * @var string[] interface classes
     */
    public array $interfaces = [];

    /**
     * @var FieldMeta[] fields in class
     */
    public array $fields = [];

    /**
     * Inner classes of this class. sash as Java
     *
     * @var array<string, ClassMeta> = [
     *     'InnerClass1' => ClassMeta,
     * ]
     */
    public array $children = [];

    /**
     * @param string $comment
     *
     * @return void
     */
    public function addComment(string $comment): void
    {
        $s = trim($comment, "/* \t");
        if ($s && !$this->description && $s[0] !== '@') {
            $this->description = $s;
        }

        $this->comments[] = $comment;
    }

    /**
     * @param ClassMeta $child
     *
     * @return void
     */
    public function addChildren(ClassMeta $child): void
    {
        $this->children[$child->name] = $child;
    }

    /**
     * @param array{inlineComment?: bool, indent?: string} $options
     *
     * @return string
     */
    public function toJSON5(array $options = []): string
    {
        $sb = StrBuilder::new("{\n");

        $indentPrefix  = $options['indent'] ?? '  ';
        $inlineComment = $options['inlineComment'] ?? false;

        foreach ($this->fields as $field) {
            if ($child = $this->children[$field->type] ?? null) {
                $value = $child->toJSON5([
                    'inlineComment' => $inlineComment,
                    'indent'        => $indentPrefix . '  ',
                ]);
            } else {
                $value = $field->exampleValue(true);
            }

            if ($inlineComment) {
                $sb->writef("%s\"%s\": %s, // %s\n", $indentPrefix, $field->name, $value, $field->comment);
                continue;
            }

            $sb->writef("%s// %s\n", $indentPrefix, $field->comment);
            $sb->writef("%s\"%s\": %s,\n", $indentPrefix, $field->name, $value);
        }

        $sb->append("$indentPrefix}");
        return $sb->getAndClear();
    }
}