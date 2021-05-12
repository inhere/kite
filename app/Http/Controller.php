<?php declare(strict_types=1);

namespace Inhere\Kite\Http;

use Inhere\Kite\Kite;

/**
 * Class Controller
 *
 * @package Inhere\Kite\Http
 */
abstract class Controller
{
    /**
     * @param string $viewPath
     * @param array  $vars
     */
    protected function render(string $viewPath, array $vars = []): void
    {
        Kite::webApp()->getRenderer()->renderOutput($viewPath, $vars);
    }
}
