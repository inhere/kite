<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate\Java;

use Toolkit\Stdlib\Type;
use function ucfirst;

/**
 * class JavaType
 */
class JavaType
{
    public const LONG = 'Long';

    public const LIST = 'List';

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
