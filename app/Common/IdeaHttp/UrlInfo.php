<?php declare(strict_types=1);

namespace Inhere\Kite\Common\IdeaHttp;

use Toolkit\Stdlib\Obj\DataObject;
use Toolkit\Stdlib\Str;
use function basename;
use const PHP_EOL;

/**
 * Class UrlInfo
 *
 * @see \parse_url()
 * @package Inhere\Kite\Common\IdeaHttp
 */
class UrlInfo extends DataObject
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

        $changeKeys = ['save', 'add', 'create', 'insert', 'update', 'edit', 'del', 'remove', 'bind'];

        foreach ($changeKeys as $key) {
            if (str_contains($path, $key)) {
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

        if (str_contains($name, '-')) {
            $name = Str::camelCase($name, $upFirst);
        }
        return    $name;
    }
}
