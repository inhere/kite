<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Defines\DataType;

use InvalidArgumentException;
use Toolkit\Stdlib\Type;
use function strtolower;

/**
 * class TypeMap
 */
class DBType
{
    /** @var string range from -2147483648 to 2147483647 */
    public const INT = 'int';

    /** @var string range from -128 to 127  */
    public const TINYINT = 'tinyint';

    /** @var string range from -32768 to 32767 */
    public const SMALLINT = 'smallint';

    /** @var string range from -8388608 to 8388607 */
    public const MEDIUMINT = 'mediumint';

    /** @var string range from -2147483648 to 2147483647 */
    public const BIGINT = 'bigint';

    /** @var string size range from 0 to 255  */
    public const CHAR = 'char';

    /** @var string size range from 0 to 65535  */
    public const VARCHAR = 'varchar';

    /** @var string size range from 0 to 65535  */
    public const TEXT = 'text';

    public const JSON = 'json';

    /** @var string size range from 0 to 4294967295  */
    public const LONGTEXT = 'longtext';

    /**
     * @param string $dbType
     *
     * @return bool
     */
    public static function isStringType(string $dbType): bool
    {
        if (str_contains($dbType, 'text')) {
            return true;
        }

        if (str_contains($dbType, 'char')) {
            return true;
        }

        return false;
    }

    /**
     * @param string $dbType
     *
     * @return bool
     */
    public static function isIntType(string $dbType): bool
    {
        if (str_contains($dbType, 'int')) {
            return true;
        }

        return false;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public static function isNoDefault(string $type): bool
    {
        return $type === self::JSON;
    }

    /**
     * @param string $dbType
     *
     * @return string
     */
    public static function toPhpType(string $dbType): string
    {
        $dbType = strtolower($dbType);
        if ($dbType === self::JSON) {
            return Type::ARRAY;
        }

        if (self::isIntType($dbType)) {
            return Type::INTEGER;
        }

        if (self::isStringType($dbType)) {
            return Type::STRING;
        }

        return $dbType;
    }

    /**
     * @param string $type db type
     *
     * @return string
     */
    public static function toUniformType(string $type): string
    {
        return match ($type) {
            self::INT, self::TINYINT, self::SMALLINT, self::MEDIUMINT => UniformType::INT,
            self::BIGINT => UniformType::INT64,
            self::CHAR, self::VARCHAR, self::TEXT, self::LONGTEXT => UniformType::STRING,
            self::JSON => UniformType::OBJECT,
            default => throw new InvalidArgumentException('un-support converted db type: ' . $type),
        };
    }
}
