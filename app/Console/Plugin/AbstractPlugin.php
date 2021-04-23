<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Plugin;

use Inhere\Kite\Console\Application;

/**
 * Class AbstractPlugin
 *
 * @package Inhere\Kite\Plugin
 */
abstract class AbstractPlugin
{
    /**
     * @return array
     */
    public function metadata(): array
    {
        return [
            // 'author' => 'inhere',
            // 'version' => '',
            // 'desc' => '',
        ];
    }

    /**
     * @param Application $app
     */
    public function run(Application $app): void
    {
        $this->exec($app);
    }

    /**
     * @param Application $app
     */
    abstract public function exec(Application $app): void;
}
