<?php declare(strict_types=1);

namespace Inhere\Kite\Http\Controller;

use Inhere\Kite\Http\Controller;
use Inhere\Kite\Model\Attr\Route;

/**
 * Class PluginController
 *
 * @package Inhere\Kite\Http\Controller
 */
#[Route(path: "/plugin")]
class PluginController extends Controller
{
    #[Route(path: "@")]
    public function index(): void
    {

    }

    #[Route(path: "view", method: "GET")]
    public function view(): void
    {

    }
}
