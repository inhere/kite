<?php declare(strict_types=1);

namespace Inhere\Kite\Common\Template;

/**
 * Interface TemplateInterface
 *
 * @package Inhere\Kite\Common\Template
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
