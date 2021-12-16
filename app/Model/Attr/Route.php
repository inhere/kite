<?php declare(strict_types=1);

namespace Inhere\Kite\Model\Attr;

use Attribute;

/**
 * Class Route
 *
 * @package Inhere\Kite\Model\Attr
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Route
{
    public string $path = '';
    public string $method = 'GET';

    /**
     * Class constructor.
     *
     * @param string $path
     * @param string $method
     */
    public function __construct(string $path = '', string $method = 'GET')
    {
        if ($path) {
            $this->path = $path;
        }

        $this->method = $method;
    }
}
