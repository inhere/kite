<?php declare(strict_types=1);

namespace Inhere\PTool\Helper;

use Inhere\Console\Util\Show;
use Toolkit\Sys\Sys;
use function trim;

/**
 * Class AppHelper
 *
 * @package Inhere\PTool\Helper
 */
class AppHelper
{
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
