<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Defines;

use Toolkit\Stdlib\Obj\BaseObject;

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

}