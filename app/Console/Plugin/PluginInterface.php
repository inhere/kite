<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Plugin;

/**
 * Interface PluginInterface
 *
 * @package Inhere\Kite\Console\Plugin
 */
interface PluginInterface
{
    public function initObj(): void;

    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * @return array
     */
    public function getMetadata(): array;
}
