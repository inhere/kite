<?php

use Inhere\Kite\Kite;
use Toolkit\Stdlib\OS;

define('BASE_PATH', dirname(__DIR__));

/** @var Composer\Autoload\ClassLoader $loader */
$loader = require dirname(__DIR__) . '/vendor/autoload.php';

// $loader->addPsr4("Toolkit\\PFlag\\", __DIR__ . '/toolkit/pflag/src/');
// $loader->addPsr4("Symfony\\Component\\Yaml\\", __DIR__ . '/symfony/yaml/');

Kite::setAliases([
    '@kite'       => Kite::basePath(),
    '@kite-user'  => OS::userHomeDir('.kite'),
    '@kite-custom' => OS::userHomeDir('.kite/custom'),
]);

return $loader;
