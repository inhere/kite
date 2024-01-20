<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Defines\DataType;

use Toolkit\Stdlib\Str;
use function in_array;

/**
 * 通用的数据类型定义
 *
 * @author inhere
 */
class UniformType
{
    public const BOOL = 'bool';

    public const STRING = 'string';

    public const INT = 'int';

    public const INT64 = 'int64';

    public const UINT = 'uint';

    public const UINT64 = 'uint64';

    public const FLOAT = 'float';

    public const DOUBLE = 'double';

    // ------ complex types ------

    public const MAP = 'map';

    public const ARRAY  = 'array';

    public const OBJECT = 'object';

    // ------ special type ------

    public const NULL = 'null';

    public const MIXED = 'mixed';

    /**
     * @param string $type
     *
     * @return bool
     */
    public static function isNumber(string $type): bool
    {
        if (self::isAnyInt($type)) {
            return true;
        }
        return self::isFloat($type);
    }

    /**
     * type is float or double
     *
     * @param string $type
     *
     * @return bool
     */
    public static function isFloat(string $type): bool
    {
        return in_array($type, [self::FLOAT, self::DOUBLE], true);
    }

    /**
     * type is any int(int, uint, int64, uint64)
     *
     * @param string $type
     *
     * @return bool
     */
    public static function isAnyInt(string $type): bool
    {
        return Str::icontains($type, self::INT);
    }

    /**
     * type is any int(int, uint)
     *
     * @param string $type
     *
     * @return bool
     */
    public static function isShortInt(string $type): bool
    {
        return in_array($type, [UniformType::INT, UniformType::UINT], true);
    }

    /**
     * is long int type(int64 or uint64)
     *
     * @param string $type
     *
     * @return bool
     */
    public static function isLongInt(string $type): bool
    {
        return in_array($type, [self::INT64, self::UINT64], true);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public static function isComplex(string $type): bool
    {
        return in_array($type, [self::MAP, self::ARRAY, self::OBJECT], true);
    }

}