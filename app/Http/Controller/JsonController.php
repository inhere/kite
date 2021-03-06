<?php declare(strict_types=1);

namespace Inhere\Kite\Http\Controller;

use Inhere\Kite\Http\Controller;

/**
 * Class HomeController
 *
 * @package Inhere\Kite\Http\Controller
 */
class JsonController extends Controller
{
    public function format(): void
    {
        $this->render('json/format');
    }

    public function json5(): void
    {
        $this->render('json/json5');
    }
}
