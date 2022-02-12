<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate;

/**
 * class JsonToPHPClass
 */
class JsonToPHPClass extends AbstractJsonToCode
{
    public const TYPE = 'php';

    /**
     * @return string
     */
    public function getLang(): string
    {
        return self::TYPE;
    }
}
