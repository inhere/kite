<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Template\Compiler;

use function addslashes;

/**
 * class PregCompiler - compile template code to php code
 *
 * @author inhere
 */
class PregCompiler extends AbstractCompiler
{
    // add slashes tag name
    private string $openTagE = '\{\{';
    private string $closeTagE = '\}\}';

    /**
     * @return static
     */
    public static function new(): self
    {
        return new self();
    }

    /**
     * @param string $open
     * @param string $close
     *
     * @return $this
     */
    public function setOpenCloseTag(string $open, string $close): self
    {
        parent::setOpenCloseTag($open, $close);

        $this->openTagE = addslashes($open);
        $this->closeTagE = addslashes($close);

        return $this;
    }

    /**
     * @param string $tplCode
     *
     * @return string
     */
    public function compile(string $tplCode): string
    {
        // TODO: Implement compile() method.
    }
}
