<?php

define('BASE_PATH', dirname(__DIR__));

require dirname(__DIR__) . '/vendor/autoload.php';

$app = new Inhere\Kite\Http\Application(BASE_PATH);

// register routes
require dirname(__DIR__) . '/app/Http/routes.php';

$app->run();

