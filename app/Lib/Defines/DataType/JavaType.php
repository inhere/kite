<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Defines\DataType;

use Toolkit\Stdlib\Type;
use function ucfirst;

/**
 * class JavaType
 */
class JavaType
{
    public const INTEGER = 'Integer';

    public const LONG = 'Long';

    public const FLOAT = 'Float';

    public const DOUBLE = 'Double';

    public const BOOLEAN = 'Boolean';

    public const STRING = 'String';

    public const LIST = 'List';

    public const MAP = 'Map';

    public const OBJECT = 'Object';

    /**
     * @param string $type
     *
     * @return string
     */
    public static function php2javaType(string $type): string
    {
        if ($type === 'int') {
            $type = Type::INTEGER;
        } elseif ($type === Type::ARRAY) {
            $type = Type::OBJECT;
        }

        return ucfirst($type);
    }
}
