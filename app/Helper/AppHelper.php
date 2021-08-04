<?php declare(strict_types=1);

namespace Inhere\Kite\Helper;

use Inhere\Console\Application;
use Inhere\Console\Util\Show;
use Toolkit\Stdlib\OS;
use Toolkit\Sys\Sys;
use function array_filter;
use function array_pop;
use function array_values;
use function defined;
use function explode;
use function getenv;
use function implode;
use function is_array;
use function putenv;
use function str_replace;
use function strpos;
use function trim;
use function vdump;
use const DIRECTORY_SEPARATOR;

/**
 * Class AppHelper
 *
 * @package Inhere\Kite\Helper
 */
class AppHelper
{
    public const LANG_MAP = [
        'zh_CN' => 'zh-CN',
    ];

    /**
     * @param string $version eg: 2.0.8, v2.0.8.1
     *
     * @return bool
     */
    public static function isVersion(string $version): bool
    {
        return 1 === preg_match('#^v?\d{1,2}.\d{1,2}.\d{1,3}(.\d{1,3})?$#', $version);
    }

    /**
     * @param string $pkgName 'inhere/console'
     *
     * @return bool
     */
    public static function isPhpPkgName(string $pkgName): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public static function isInPhar(): bool
    {
        if (defined('IN_PHAR')) {
            return IN_PHAR;
        }
        return false;
    }

    /**
     * @param string $tag
     *
     * @return string
     */
    public static function formatTag(string $tag): string
    {
        $tag = trim($tag, 'v ');
        if (!$tag) {
            return '';
        }

        return 'v' . $tag;
    }

    /**
     * env: LC_CTYPE=zh_CN.UTF-8
     *
     * @param string $default
     *
     * @return string
     */
    public static function getLangFromENV(string $default = ''): string
    {
        $value = (string)getenv('LC_CTYPE');

        // zh_CN.UTF-8
        if (strpos($value, '.') > 0) {
            [$value,] = explode('.', $value);

            return self::LANG_MAP[$value] ?? $value;
        }

        return $default;
    }

    /**
     * Open browser URL
     *
     * Mac：
     * open 'https://swoft.org'
     *
     * Linux:
     * x-www-browser 'https://swoft.org'
     *
     * Windows:
     * cmd /c start https://swoft.org
     *
     * @param string $pageUrl
     */
    public static function openBrowser(string $pageUrl): void
    {
        if (Sys::isMac()) {
            $cmd = "open \"{$pageUrl}\"";
        } elseif (Sys::isWin()) {
            // $cmd = 'cmd /c start';
            $cmd = "start {$pageUrl}";
        } else {
            $cmd = "x-www-browser \"{$pageUrl}\"";
        }

        Show::info("Will open the page on browser:\n  $pageUrl");

        // Show::writeln("> $cmd");
        Sys::execute($cmd);
    }

    /**
     * @param Application $app
     */
    public static function loadOsEnvInfo(Application $app): void
    {
        $osEnv = $app->getParam('osEnv', []);
        if (!$osEnv || !is_array($osEnv)) {
            return;
        }

        Show::aList($osEnv, 'Put ENV From Config: "osEnv"', [
            'ucFirst'      => false,
            'ucTitleWords' => false,
        ]);
        // Sys::setOSEnv() TODO
        foreach ($osEnv as $name => $value) {
            putenv("$name=$value");
        }
    }

    /**
     * @param string $path
     *
     * @return string
     * @see realpath()
     * @link https://www.php.net/manual/zh/function.realpath.php#84012
     * @deprecated  use FS::realpath()
     */
    public static function realpath(string $path): string
    {
        $path  = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        if (!$parts = array_values($parts)) {
            return '';
        }

        $start  = '';
        $isUnix = DIRECTORY_SEPARATOR === '/';
        if ($isUnix) {
            // ~: is user home dir in *nix OS
            if ($parts[0] === '~') {
                $parts[0] = OS::getUserHomeDir();
            } else {
                $start = '/';
            }
        }

        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' === $part) {
                continue;
            }

            if ('..' === $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }

        return $start . implode(DIRECTORY_SEPARATOR, $absolutes);
    }

}
