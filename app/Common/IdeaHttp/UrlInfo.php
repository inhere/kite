<?php declare(strict_types=1);

namespace Inhere\Kite\Common\IdeaHttp;

use Inhere\Kite\Common\MapObject;

/**
 * Class UrlInfo
 *
 * @see \parse_url()
 * @package Inhere\Kite\Common\IdeaHttp
 */
class UrlInfo extends MapObject
{
    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->getString('path');
    }
}
