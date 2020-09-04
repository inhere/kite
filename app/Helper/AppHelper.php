<?php declare(strict_types=1);

namespace Inhere\Kite\Helper;

use Inhere\Console\Util\Show;
use Toolkit\Sys\Sys;
use function explode;
use function getenv;
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
            [$value, ] = explode('.', $value);

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
            $cmd = 'open';
        } elseif (Sys::isWin()) {
            $cmd = 'cmd /c start';
        } else {
            $cmd = 'x-www-browser';
        }

        Show::info("Will open the page on browser:\n  $pageUrl");
        Sys::execute($cmd . " \"{$pageUrl}\"");
    }

    /**
     * @param array $userConfig
     * @param array $config
     *
     * @return array
     */
    public static function mergeConfig(array $userConfig, array $config): array
    {
        foreach ($userConfig as $key => $item) {
            if (isset($config[$key]) && is_array($config[$key])) {
                if (is_array($item)) {
                    $config[$key] = array_merge($config[$key], $item);
                } else {
                    throw new \RuntimeException("Array config error! the '{$key}' must be an array");
                }
            } else {
                // custom add/set config
                $config[$key] = $item;
            }
        }

        return $config;
    }
}
