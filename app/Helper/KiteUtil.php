<?php declare(strict_types=1);

namespace Inhere\Kite\Helper;

use function dirname;
use function is_file;

/**
 * class KiteUtil
 */
class KiteUtil
{
    /**
     * @param string $dir
     *
     * @return string
     */
    public static function findPhpUnitConfigFile(string $dir): string
    {
        while (true) {
            if (!$dir) {
                break;
            }

            if (
                is_file($dir . '/phpunit.xml') ||
                is_file($dir . '/phpunit.xml.dist')
            ) {
                break;
            }

            $dir = dirname($dir);
        }

        return $dir;
    }
}
