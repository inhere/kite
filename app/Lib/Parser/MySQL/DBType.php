<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Parser\MySQL;

use Inhere\Kite\Lib\Generate\Java\JavaType;
use Toolkit\Stdlib\Str;
use Toolkit\Stdlib\Type;
use function strtolower;

/**
 * class TypeMap
 */
class DBType
{
    public const INT = 'int';

    public const BIGINT = 'bigint';

    public const CHAR = 'char';

    public const VARCHAR = 'varchar';

    public const TEXT = 'text';

    public const JSON = 'json';

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
}
