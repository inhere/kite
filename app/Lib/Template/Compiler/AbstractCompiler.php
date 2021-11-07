<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Template\Compiler;

use Inhere\Kite\Lib\Template\Contract\CompilerInterface;

/**
 * class AbstractCompiler
 *
 * @author inhere
 */
abstract class AbstractCompiler implements CompilerInterface
{
    public string $openTag = '{{';
    public string $closeTag = '}}';

    /**
     * custom directive, control statement token.
     *
     * eg: implement include()
     *
     * @var array
     */
    public array $customTokens = [];

    /**
     * @param string $open
     * @param string $close
     *
     * @return $this
     */
    public function setOpenCloseTag(string $open, string $close): self
    {
        $this->openTag = $open;
        $this->closeTag = $close;

        return $this;
    }

}
