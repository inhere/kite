<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Template;

use Inhere\Kite\Lib\Template\Contract\TemplateInterface;

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
    protected $globalVars = [];

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
