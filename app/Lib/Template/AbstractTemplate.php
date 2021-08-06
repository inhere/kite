<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Template;

/**
 * Class AbstractTemplate
 *
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