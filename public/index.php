<?php declare(strict_types=1);

use Inhere\Kite\Http\WebApplication;

error_reporting(E_STRICT);

require dirname(__DIR__) . '/app/boot.php';

$app = new WebApplication(BASE_PATH);

// register routes
require dirname(__DIR__) . '/app/Http/routes.php';

$app->run();
