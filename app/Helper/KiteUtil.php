<?php declare(strict_types=1);

namespace Inhere\Kite\Helper;

use Leuffen\TextTemplate\TextTemplate;
use Toolkit\FsUtil\File;
use Toolkit\FsUtil\FS;
use Toolkit\Stdlib\OS;
use function dirname;
use function is_file;
use function is_string;

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
        $tplEng->addFilter('default', function ($value, $default) {
            if (is_string($value) && $value === '') {
                return $default;
            }

            return empty($value) ? $default : $value;
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
