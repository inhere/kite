<?php declare(strict_types=1);

namespace Inhere\Kite\Common\IdeaHttp;

use Inhere\Kite\Common\MapObject;
use Toolkit\Stdlib\Str;
use function basename;
use function strpos;
use const PHP_EOL;

/**
 * Class UrlInfo
 *
 * @see \parse_url()
 * @package Inhere\Kite\Common\IdeaHttp
 */
class UrlInfo extends MapObject
{
    /**
     * @param bool $newline
     *
     * @return string
     */
    public function getPath(bool $newline = false): string
    {
        $path = $this->getString('path');

        return $newline ? $path . PHP_EOL : $path;
    }

    /**
     * @return bool
     */
    public function isChangeDataUri(): bool
    {
        $path = $this->getString('path');

        $changeKeys = ['save', 'add', 'create', 'insert', 'update', 'edit', 'del', 'remove'];

        foreach ($changeKeys as $key) {
            if (strpos($path, $key) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param bool $upFirst
     *
     * @return string
     */
    public function getShortName(bool $upFirst = false): string
    {
        $path = $this->getString('path');

        $sName = basename($path);
        return Str::camelCase($sName, $upFirst);
    }

    /**
     * @param bool $upFirst
     *
     * @return string
     */
    public function pathToName(bool $upFirst = false): string
    {
        $path = $this->getString('path');
        $name = Str::camelCase($path, $upFirst, '/');

        if (strpos($name, '-') !== false) {
            $name = Str::camelCase($name, $upFirst);
        }
        return    $name;
    }
}
