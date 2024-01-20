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
    public static function toUniformType(string $type, bool $userObject = true): string
    {
        return match ($type) {
            self::BOOL, self::BOOLEAN => UniformType::BOOL,
            self::INT, self::INTEGER => UniformType::INT,
            self::STRING => UniformType::STRING,
            self::FLOAT => UniformType::FLOAT,
            self::DOUBLE => UniformType::DOUBLE,
            self::NULL => UniformType::NULL,
            self::ARRAY => UniformType::ARRAY,
            // self::MAP => UniformType::MAP,
            self::OBJECT => UniformType::OBJECT,
            self::MiXED => UniformType::MIXED,
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