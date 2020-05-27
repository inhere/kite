<?php declare(strict_types=1);

namespace Inhere\PTool\Helper;

use Inhere\Console\Util\Show;
use Toolkit\Sys\Sys;
use function explode;
use function getenv;
use function strpos;
use function trim;

/**
 * Class AppHelper
 *
 * @package Inhere\PTool\Helper
 */
class AppHelper
{
    public const LANG_MAP = [
        'zh_CN' => 'zh-CN',
    ];

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
        $value = getenv('LC_CTYPE');

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

        Show::info("Will open the page on browser: $pageUrl");
        Sys::execute($cmd . ' ' . $pageUrl);
    }
}
