<?php
/**
 * register services to web-app
 *
 * @var Toolkit\Stdlib\Obj\ObjectBox $box
 */

use Inhere\Route\Dispatcher\Dispatcher;
use Inhere\Route\Router;
use PhpPkg\EasyTpl\EasyTemplate;

$box->set('webRouter', function () {
    return new Router([
        'name' => 'kite-router',
    ]);
});

$box->set('renderer', function () {
    $config = $this->config()->getArray('renderer');
    return new EasyTemplate($config);
});

$box->set('dispatcher', [
    'class'   => Dispatcher::class,
    // prop settings
    'options' => [
        'actionSuffix' => '',
    ],
]);
