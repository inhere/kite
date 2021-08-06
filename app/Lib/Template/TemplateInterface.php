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
     * @param string $tempFile
     * @param array  $vars
     *
     * @return string
     */
    public function renderFile(string $tempFile, array $vars): string;

    /**
     * @param string $tplCode
     * @param array  $vars
     *
     * @return string
     */
    public function renderString(string $tplCode, array $vars): string;
}
