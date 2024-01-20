<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser\Java;

use Inhere\Kite\Lib\Defines\DataType\JavaType;
use Inhere\Kite\Lib\Defines\FieldMeta;

/**
 * @author inhere
 */
class JavaField extends FieldMeta
{
    /**
     * @var array annotations on field
     */
    public array $annotations = [];

    /**
     * @var string
     */
    public string $accessModifier = '';

    /**
     * @var string[] other modifiers: final|static
     */
    public array $otherModifiers = [];

    /**
     * get uniform type
     *
     * @return string
     */
    protected function toUniformType(): string
    {
        return JavaType::toUniformType($this->type);
    }

    public function toArray(): array
    {
        $map = parent::toArray();
        // append
        $map['annotations'] = $this->annotations;
        $map['accessModifier'] = $this->accessModifier;
        $map['otherModifiers'] = $this->otherModifiers;

        return $map;
    }

}
