<?php declare(strict_types=1);

namespace Inhere\Kite\Concern;

use InvalidArgumentException;
use function explode;
use function str_contains;
use function str_replace;

/**
 * Class PathAliasTrait
 */
trait StaticPathAliasTrait
{
    /**
     * @var array
     */
    protected static array $aliases = [];

    /**
     * get real value by alias
     *
     * @param string $alias
     * @return string
     */
    public static function alias(string $alias, bool $throwEx = false): string
    {
        // Not an alias
        if (!$alias || $alias[0] !== '@') {
            return $alias;
        }

        $sep  = '/';
        $other = '';
        $alias = str_replace('\\', $sep, $alias);

        // have other partial. e.g: @project/temp/logs
        if (str_contains($alias, $sep)) {
            [$alias, $other] = explode($sep, $alias, 2);
        }

        if (!isset(self::$aliases[$alias])) {
            if ($throwEx) {
                throw new InvalidArgumentException("The alias '$alias' is not registered!");
            }

            return $alias;
        }

        return self::$aliases[$alias] . ($other ? $sep . $other : '');
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function hasAlias(string $name): bool
    {
        return isset(self::$aliases[$name]);
    }

    /**
     * @param string $alias
     * @param string $value
     * @throws InvalidArgumentException
     */
    public static function setAlias(string $alias, string $value): void
    {
        self::$aliases[$alias] = self::alias($value);
    }

    /**
     * @param array $aliases
     * @throws InvalidArgumentException
     */
    public static function setAliases(array $aliases): void
    {
        foreach ($aliases as $alias => $realPath) {
            // the 1th char must is '@'
            if (!$alias || $alias[0] !== '@') {
                continue;
            }

            self::$aliases[$alias] = self::alias($realPath);
        }
    }

    /**
     * @return array
     */
    public static function getAliases(): array
    {
        return self::$aliases;
    }
}
