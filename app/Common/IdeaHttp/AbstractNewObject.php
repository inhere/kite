<?php declare(strict_types=1);

namespace Inhere\Kite\Common\IdeaHttp;

/**
 * Class AbstractNewObject
 *
 * @package Inhere\Kite\Common\IdeaHttp
 */
abstract class AbstractNewObject
{
    /**
     * @return static
     */
    public static function new()
    {
        return new static();
    }
}
