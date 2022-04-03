<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate\Handler;

use Inhere\Kite\Lib\Generate\AbstractGenCode;
use Inhere\Kite\Lib\Generate\GenCodeFactory;

/**
 * class GenGoStruct
 *
 * @author inhere
 */
class GenGoStruct extends AbstractGenCode
{
    public const TYPE = GenCodeFactory::LANG_GO;

    public function getLang(): string
    {
        return GenCodeFactory::LANG_GO;
    }
}
