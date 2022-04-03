<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate;

use Inhere\Kite\Lib\Generate\Handler\GenGoStruct;
use Inhere\Kite\Lib\Generate\Handler\GenJavaClass;
use Inhere\Kite\Lib\Generate\Handler\GenPhpClass;
use InvalidArgumentException;

/**
 * class GenCodeFactory
 *
 * @author inhere
 */
class GenCodeFactory
{
    public const LANG_PHP  = 'php';
    public const LANG_JAVA = 'java';
    public const LANG_GO   = 'go';

    public const LANG2HANDLER_CLASS = [
        self::LANG_PHP  => GenPhpClass::class,
        self::LANG_JAVA => GenJavaClass::class,
        self::LANG_GO   => GenGoStruct::class,
    ];

    /**
     * @param string $lang
     *
     * @return AbstractGenCode
     */
    public static function create(string $lang = self::LANG_PHP): AbstractGenCode
    {
        if (!isset(self::LANG2HANDLER_CLASS[$lang])) {
            throw new InvalidArgumentException('invalid lang: ' . $lang);
        }

        $class = self::LANG2HANDLER_CLASS[$lang];

        return new $class;
    }
}
