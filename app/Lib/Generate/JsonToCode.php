<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate;

/**
 * class JsonToCode
 */
class JsonToCode
{
    /**
     * @param string $lang
     *
     */
    public static function create(string $lang): DTOGenerator
    {
        return new DTOGenerator(['lang' => $lang]);
    }
}
