<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser\Java;

use Inhere\Kite\Lib\Defines\DataType\JavaType;
use Inhere\Kite\Lib\Defines\FieldMeta;
use function substr;

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
     * @var string access modifier. eg: public
     */
    public string $accessModifier = '';

    /**
     * @var string[] other modifiers: final|static
     */
    public array $otherModifiers = [];

    /**
     * @param string $type
     *
     * @return void
     */
    public function setType(string $type): void
    {
        if (str_contains($type, '<')) {
            $str = $type;
            $pos1 = strpos($str, '<');
            $type = substr($type, 0, $pos1);

            // sub type
            if ($type === JavaType::LIST) {
                $this->subType = substr($str, $pos1+1, -1);
            }
        }

        $this->type = $type;
    }

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
