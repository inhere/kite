<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Template;

use Inhere\Kite\Lib\Template\Contract\TemplateInterface;
use Toolkit\Stdlib\Obj;

/**
 * Class AbstractTemplate
 *
 * @author inhere
 * @package Inhere\Kite\Lib\Template
 */
abstract class AbstractTemplate implements TemplateInterface
{
    /**
     * @var array
     */
    protected array $globalVars = [];

    /**
     * @return static
     */
    public static function new(array $config = []): self
    {
        return new static($config);
    }

    /**
     * Class constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        Obj::init($this, $config);
    }

    /**
     * @return array
     */
    public function getGlobalVars(): array
    {
        return $this->globalVars;
    }

    /**
     * @param array $globalVars
     */
    public function setGlobalVars(array $globalVars): void
    {
        $this->globalVars = $globalVars;
    }

}
