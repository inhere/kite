<?php declare(strict_types=1);

namespace Inhere\Kite\Http\Controller;

use Inhere\Kite\Http\Controller;
use Inhere\Kite\Kite;

/**
 * Class HomeController
 *
 * @package Inhere\Kite\Http\Controller
 */
class HomeController extends Controller
{
    public function index(): void
    {
        $this->render('home');
    }

    public function routes(): void
    {
        $str = Kite::webRouter()->toString();

        $this->renderHTML(<<<HTML
<h1>Routes:</h1>
<pre>$str</pre>
HTML
        );
    }
}
