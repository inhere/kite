<?php declare(strict_types=1);

namespace Inhere\PTool\Helper;

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
}
