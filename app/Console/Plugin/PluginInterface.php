<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Plugin;

/**
 * Interface PluginInterface
 *
 * @package Inhere\Kite\Console\Plugin
 */
interface PluginInterface
{
    public function init(): void;
    public function metadata(): array;
    public function options(): array;
}
