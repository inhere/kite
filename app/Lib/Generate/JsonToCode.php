<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate;

/**
 * class JsonToCode
 */
class JsonToCode
{
    /**
     * @param string $type
     *
     * @return AbstractJsonToCode
     */
    public static function create(string $type = 'php'): AbstractJsonToCode
    {
        if ($type === JsonToPHPClass::TYPE) {
            return new JsonToPHPClass();
        }

        if ($type === JsonToGoStruct::TYPE) {
            return new JsonToGoStruct();
        }

        return new JsonToJavaClass();
    }
}
