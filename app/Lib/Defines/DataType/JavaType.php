<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Defines\DataType;

use InvalidArgumentException;
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

    public const NULL = 'null';

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

    /**
     * @param string $type java type
     * @param bool   $userObject type as user class
     *
     * @return string
     */
    public static function toUniType(string $type, bool $userObject = true): string
    {
        return match ($type) {
            self::INTEGER => UniType::INT,
            self::LONG => UniType::INT64,
            self::FLOAT => UniType::FLOAT,
            self::DOUBLE => UniType::DOUBLE,
            self::BOOLEAN => UniType::BOOL,
            self::STRING => UniType::STRING,
            self::NULL => UniType::NULL,
            self::LIST => UniType::ARRAY,
            self::MAP => UniType::MAP,
            self::OBJECT => UniType::OBJECT,
            default => $userObject ? ucfirst($type) : throw new InvalidArgumentException('un-support java type: ' . $type),
        };
    }

    /**
     * @param string $uType
     *
     * @return string
     */
    public static function fromUniType(string $uType): string
    {
        return match ($uType) {
            UniType::INT => self::INTEGER,
            UniType::INT64 => self::LONG,
            UniType::FLOAT => self::FLOAT,
            UniType::DOUBLE => self::DOUBLE,
            UniType::BOOL => self::BOOLEAN,
            UniType::STRING => self::STRING,
            UniType::NULL => self::NULL,
            UniType::ARRAY => self::LIST,
            UniType::MAP => self::MAP,
            UniType::OBJECT => self::OBJECT,
            default => $uType,
        };
    }

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
        return in_array($type, [self::INTEGER, self::LONG], true);
    }

}
