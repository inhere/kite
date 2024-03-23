<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Defines\DataField;

use Inhere\Kite\Lib\Defines\DataType\PhpType;
use Inhere\Kite\Lib\Defines\FieldMeta;

/**
 * metadata of php class field
 *
 * @author inhere
 */
class PhpField extends FieldMeta
{

    /**
     * get universal type
     *
     * @return string
     */
    public function toUniType(): string
    {
        return PhpType::toUniType($this->type);
    }

}