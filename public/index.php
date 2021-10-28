<?php

require dirname(__DIR__) . '/app/boot.php';

$app = new Inhere\Kite\Http\WebApplication(BASE_PATH);

// register routes
require dirname(__DIR__) . '/app/Http/routes.php';

$app->run();
