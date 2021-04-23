<?php declare(strict_types=1);

namespace Inhere\Kite\Plugin;

use Inhere\Kite\Console\Application;

/**
 * Class AbstractPlugin
 *
 * @package Inhere\Kite\Plugin
 */
abstract class AbstractPlugin
{
    abstract public function exec(Application $app): void;
}
