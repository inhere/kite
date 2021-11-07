<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Template\Contract;

/**
 * Interface TemplateInterface
 *
 * @author inhere
 * @package Inhere\Kite\Lib\Template\Contract
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
