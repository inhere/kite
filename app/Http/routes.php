<?php declare(strict_types=1);

/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

use Inhere\Kite\Http\Controller\HomeController;
use Inhere\Kite\Http\Controller\JsonController;
use Inhere\Kite\Kite;

$router = Kite::webRouter();

$router->get('/', HomeController::class . '@index');
$router->get('/routes', HomeController::class . '@routes');
$router->get('/json5', JsonController::class . '@json5');
$router->get('/json', JsonController::class . '@format');

// vdump($router->match('/'));exit;