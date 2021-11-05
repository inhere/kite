<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate\Java;

/**
 * class JavaType
 */
class JavaType
{
    public const LONG = 'Long';

    public const OBJECT = 'Object';

    /**
     * @param string $type
     *
     * @return string
     */
    public static function php2javaType(string $type): string
    {
        return $type;
    }
}
