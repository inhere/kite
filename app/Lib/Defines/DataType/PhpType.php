<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Defines\DataType;

use InvalidArgumentException;
use Toolkit\Stdlib\Type;

/**
 * @author inhere
 */
class PhpType extends Type
{
    /**
     * @param string $type Php type
     * @param bool $userObject type as user class
     *
     * @return string
     */
    public static function toUniType(string $type, bool $userObject = true): string
    {
        return match ($type) {
            self::BOOL, self::BOOLEAN => UniType::BOOL,
            self::INT, self::INTEGER => UniType::INT,
            self::STRING => UniType::STRING,
            self::FLOAT => UniType::FLOAT,
            self::DOUBLE => UniType::DOUBLE,
            self::NULL => UniType::NULL,
            self::ARRAY => UniType::ARRAY,
            // self::MAP => UniType::MAP,
            self::OBJECT => UniType::OBJECT,
            self::MiXED => UniType::MIXED,
            default => $userObject ? ucfirst($type) : throw new InvalidArgumentException('un-support converted php type: ' . $type),
        };
    }

    /**
     * @param string $type Php type
     *
     * @return string
     */
    public static function toJavaType(string $type): string
    {
        if (self::isInt($type)) {
            $type = JavaType::INTEGER;
        } elseif ($type === self::ARRAY) {
            $type = JavaType::OBJECT;
        }

        return ucfirst($type);
    }
}