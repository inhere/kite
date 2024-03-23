<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Defines\DataType;

use InvalidArgumentException;

/**
 * class GolangType
 *
 * @author inhere
 */
class GoType
{
    public const BOOL = 'bool';

    public const INT = 'int';

    public const INT8 = 'int8';

    public const INT16 = 'int16';

    public const INT32 = 'int32';

    public const INT64 = 'int64';

    public const UINT = 'uint';

    public const UINT8 = 'uint8';

    public const UINT16 = 'uint16';

    public const UINT32 = 'uint32';

    public const UINT64 = 'uint64';

    public const FLOAT32 = 'float32';

    public const FLOAT64 = 'float64';

    public const STRING = 'string';

    // ------ complex types ------

    public const ARRAY  = 'array';

    public const SLICE = 'slice';

    public const MAP = 'map';

    public const STRUCT = 'struct';

    // ------ special type ------

    public const NIL = 'nil';

    public const ANY = 'interface{}';

    /**
     * @param string $type go type
     * @param bool $userObject type as user class
     *
     * @return string
     */
    public static function toUniType(string $type, bool $userObject = true): string
    {
        return match ($type) {
            self::INT, self::INT8, self::INT16, self::INT32 => UniType::INT,
            self::UINT, self::UINT8, self::UINT16, self::UINT32 => UniType::UINT,
            self::INT64 => UniType::INT64,
            self::UINT64 => UniType::UINT64,
            self::FLOAT32 => UniType::FLOAT,
            self::FLOAT64 => UniType::DOUBLE,
            self::STRING => UniType::STRING,
            self::ANY => UniType::MIXED,
            self::NIL => UniType::NULL,
            default => $userObject ? ucfirst($type) : throw new InvalidArgumentException('un-support converted go type: ' . $type),
        };
    }
}
