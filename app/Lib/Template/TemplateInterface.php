<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Template;

/**
 * Interface TemplateInterface
 *
 * @package Inhere\Kite\Lib\Template
 */
interface TemplateInterface
{
    /**
     * @param string $tplFile
     * @param array  $tplVars
     *
     * @return string
     */
    public function renderFile(string $tplFile, array $tplVars): string;

    /**
     * @param string $tplCode
     * @param array  $tplVars
     *
     * @return string
     */
    public function renderString(string $tplCode, array $tplVars): string;
}
