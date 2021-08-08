<?php declare(strict_types=1);

namespace Inhere\Kite\Helper;

use Inhere\Console\Application;
use Inhere\Console\Util\Show;
use Toolkit\Stdlib\OS;
use Toolkit\Sys\Sys;
use function defined;
use function explode;
use function getenv;
use function is_array;
use function putenv;
use function random_int;
use function strlen;
use function strpos;
use function trim;

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
     * @return string eg: ~/.config/kite.php
     */
    public static function userConfigDir(string $path = ''): string
    {
        return OS::getUserHomeDir() . '/.config' . ($path ? "/$path" : '');
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public static function userHomeDir(string $path = ''): string
    {
        return OS::getUserHomeDir() . ($path ? "/$path" : '');
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public static function userCacheDir(string $path = ''): string
    {
        return OS::getUserHomeDir() . '/.cache' . ($path ? "/$path" : '');
    }

    /**
     * @param string $sname
     * @param int    $length
     *
     * @return string
     * @throws \Exception
     */
    public static function genRandomStr(string $sname, int $length): string
    {
        $samples = [
            'alpha'        => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
            'alpha_num'    => '0123456789abcdefghijklmnopqrstuvwxyz',
            'alpha_num_up' => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
            'alpha_num_c'  => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-+!@#$%&*',
        ];

        $chars = $samples[$sname] ?? $samples['alpha_num'];

        $str = '';
        $max = strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[random_int(0, $max)];
        }

        return $str;
    }

}
