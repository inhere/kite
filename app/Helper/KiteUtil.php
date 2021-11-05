<?php declare(strict_types=1);

namespace Inhere\Kite\Helper;

use Leuffen\TextTemplate\TextTemplate;
use Toolkit\FsUtil\FS;
use Toolkit\Stdlib\OS;
use Toolkit\Stdlib\Str;
use function dirname;
use function is_file;

/**
 * class KiteUtil
 */
class KiteUtil
{

    /**
     * @param string $path
     *
     * @return string eg: ~/.config/kite.php
     */
    public static function userConfigDir(string $path = ''): string
    {
        return OS::getUserHomeDir() . '/.config' . ($path ? "/$path" : '');
    }

    /**
     * @param string $text
     *
     * @return TextTemplate
     */
    public static function newTplEngine(string $text): TextTemplate
    {
        $tplEng = new TextTemplate($text);
        // default value on empty. usage: {= ctx.user | default:inhere}
        $tplEng->addFilter('default', function ($value, $default) {
            if ($value === '') {
                return $default;
            }
            return empty($value) ? $default : $value;
        });

        // upper first char. usage: {= ctx.user | upFirst}
        $tplEng->addFilter('upFirst', function ($value) {
            if ($value === '') {
                return '';
            }
            return Str::upFirst($value);
        });

        // snake to camel. usage: {= ctx.user | toCamel}
        $tplEng->addFilter('toCamel', function ($value) {
            if ($value === '') {
                return '';
            }
            return Str::toCamel($value);
        });

        // camel to snake. usage: {= ctx.user | toSnake}
        $tplEng->addFilter('toSnake', function ($value) {
            if ($value === '') {
                return '';
            }
            return Str::toSnake($value);
        });

        return $tplEng;
    }

    /**
     * @param string $dir
     *
     * @return string
     */
    public static function findPhpUnitConfigFile(string $dir): string
    {
        $dir = FS::realpath($dir);

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
