<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Template;

use Inhere\Kite\Lib\Template\Contract\CompilerInterface;

/**
 * class Compiler - compile template code to php code
 *
 * @author inhere
 */
class Compiler implements CompilerInterface
{
    /**
     * @return static
     */
    public static function new(): self
    {
        return new self();
    }
}
