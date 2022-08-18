<?php declare(strict_types=1);

namespace Inhere\Kite\Http;

use Inhere\Kite\Kite;
use function header;

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
        Kite::webApp()->getRenderer()->render($viewPath, $vars);
    }

    /**
     * @param string $html
     *
     * @return void
     */
    protected function renderHTML(string $html): void
    {
        header('Content-Type: text/html');
        echo $html;
    }
}
