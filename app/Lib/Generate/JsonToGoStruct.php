<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate;

/**
 * class JsonToGoStruct
 */
class JsonToGoStruct extends AbstractJsonToCode
{
    public const TYPE = 'go';

    /**
     * @return string
     */
    public function getLang(): string
    {
        return self::TYPE;
    }
}
