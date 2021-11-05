<?php

use Inhere\Kite\Kite;
use Toolkit\Stdlib\OS;

define('BASE_PATH', dirname(__DIR__));
defined('IN_PHAR') || define('IN_PHAR', false);

/** @var Composer\Autoload\ClassLoader $loader */
$loader = require dirname(__DIR__) . '/vendor/autoload.php';

// $loader->addPsr4("Toolkit\\PFlag\\", __DIR__ . '/toolkit/pflag/src/');
// $loader->addPsr4("Symfony\\Component\\Yaml\\", __DIR__ . '/symfony/yaml/');

// user kite dirs
Kite::setAliases([
    '@user-kite'    => OS::userHomeDir('.kite'),
    '@user-tmp'     => OS::userHomeDir('.kite/tmp'),
    '@user-res'     => OS::userHomeDir('.kite/resource'),
    '@user-custom'  => OS::userHomeDir('.kite/custom'),
]);

// kite dir
// if (IN_PHAR) {
//
// } else {
//
// }

Kite::setAliases([
    '@kite'         => Kite::basePath(),
    '@tmp'          => Kite::getPath('tmp'),
    '@resource'     => Kite::getPath('resource'),
    '@resource-tpl' => Kite::getPath('resource/templates'),
    '@custom'       => Kite::getPath('custom'),
]);

return $loader;
