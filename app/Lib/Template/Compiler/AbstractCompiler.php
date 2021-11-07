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
    public const PHP_TAG_OPEN  = '<?php';
    public const PHP_TAG_ECHO  = '<?';
    public const PHP_TAG_ECHO1 = '<?=';
    public const PHP_TAG_CLOSE = '?>';

    public string $openTag = '{{';
    public string $closeTag = '}}';

    /**
     * custom filter for handle result.
     *
     * @var array{string, callable(mixed): string}
     */
    public array $customFilters = [];

    /**
     * custom directive, control statement token.
     *
     * eg: implement include()
     *
     * @var array{string, callable(string, string): string}
     */
    public array $customDirectives = [];

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

    /**
     * @param string $name
     * @param callable $filter
     *
     * @return $this
     */
    public function addFilter(string $name, callable $filter): self
    {
        $this->customFilters[$name] = $filter;
        return $this;
    }

    /**
     * @param string $name
     * @param callable $handler
     *
     * @return $this
     */
    public function addDirective(string $name, callable $handler): self
    {
        $this->customDirectives[$name] = $handler;
        return $this;
    }
}
