<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Template\Compiler;

use Inhere\Kite\Lib\Template\Contract\CompilerInterface;
use Toolkit\Stdlib\Str;
use function array_shift;
use function explode;
use function htmlspecialchars;
use function implode;
use function in_array;
use function sprintf;
use function str_contains;
use function strlen;

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

    public const RAW_OUTPUT = 'raw';

    /**
     * @var string
     */
    public string $filterSep = '|';

    /**
     * @var callable|callable-string
     * @see htmlspecialchars()
     */
    public $echoFilterFunc = 'htmlspecialchars';

    /**
     * custom filter name and expr mapping
     *
     * ```php
     * [
     *  'upper'   => 'strtoupper(',
     *  'filter1' => '$this->applyFilter("filter1", ',
     * ]
     * ```
     *
     * @var array<string, string>
     */
    public array $filterMapping = [];

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
        $this->openTag  = $open;
        $this->closeTag = $close;

        return $this;
    }

    /**
     * @param string $echoBody
     *
     * @return string
     */
    protected function parseInlineFilters(string $echoBody): string
    {
        if (!$this->filterSep) {
            return $echoBody;
        }

        $filters = Str::explode($echoBody, $this->filterSep);
        $newExpr = array_shift($filters);

        $coreFn = $this->echoFilterFunc;
        if (
            $coreFn
            && $coreFn !== self::RAW_OUTPUT
            && !in_array(self::RAW_OUTPUT, $filters, true)
        ) {
            $newExpr = sprintf('%s(%s)', $coreFn, $newExpr);
        }

        foreach ($filters as $filter) {
            if ($filter === self::RAW_OUTPUT) {
                continue;
            }

            if (str_contains($filter, ':')) {
                [$filter, $argStr] = explode(':', $filter, 2);
                if (strlen($argStr) > 1 && str_contains($argStr, ',')) {
                    $args = Str::toTypedList($argStr);
                } else {
                    $args = [Str::toTyped($argStr, true)];
                }

                $filter  = $this->filterMapping[$filter] ?? $filter . '(';
                $newExpr = sprintf('%s%s, %s)', $filter, $newExpr, implode(',', $args));
            } else {
                $filter  = $this->filterMapping[$filter] ?? $filter . '(';
                $newExpr = sprintf('%s%s)', $filter, $newExpr);
            }
        }

        return $newExpr;
    }

    /**
     * @param string $name
     * @param string $callExpr
     *
     * @return $this
     */
    public function addFilter(string $name, string $callExpr): self
    {
        $callExpr = str_contains($callExpr, '(') ? $callExpr : $callExpr . '(';

        $this->filterMapping[$name] = $callExpr;

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

    /**
     * @return $this
     */
    public function disableEchoFilter(): self
    {
        $this->echoFilterFunc = self::RAW_OUTPUT;
        return $this;
    }

    /**
     * @param callable $echoFilterFunc
     *
     * @return AbstractCompiler
     */
    public function setEchoFilterFunc(callable $echoFilterFunc): self
    {
        $this->echoFilterFunc = $echoFilterFunc;
        return $this;
    }
}
