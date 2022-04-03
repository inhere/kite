<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Generate\Handler;

use Inhere\Kite\Lib\Generate\AbstractGenCode;
use Inhere\Kite\Lib\Generate\GenCodeFactory;

/**
 * class GenPhpClass
 *
 * @author inhere
 */
class GenPhpClass extends AbstractGenCode
{
    public const TYPE = GenCodeFactory::LANG_PHP;

    public function getLang(): string
    {
        return GenCodeFactory::LANG_PHP;
    }
}
