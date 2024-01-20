<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Defines\DataField;

use Inhere\Kite\Lib\Defines\DataType\GoType;
use Inhere\Kite\Lib\Defines\FieldMeta;

/**
 * metadata of Go struct field
 *
 * @author inhere
 */
class GoField extends FieldMeta
{

    /**
     * get uniform type
     *
     * @return string
     */
    public function toUniformType(): string
    {
        return GoType::toUniformType($this->type);
    }

}