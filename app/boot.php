<?php

use Inhere\Kite\Kite;
use Toolkit\Stdlib\OS;

define('BASE_PATH', dirname(__DIR__));

/** @var Composer\Autoload\ClassLoader $loader */
$loader = require dirname(__DIR__) . '/vendor/autoload.php';

// $loader->addPsr4("Toolkit\\PFlag\\", __DIR__ . '/toolkit/pflag/src/');
// $loader->addPsr4("Symfony\\Component\\Yaml\\", __DIR__ . '/symfony/yaml/');

Kite::setAliases([
    '@kite'          => Kite::basePath(),
    '@kite-tmp'      => Kite::getPath('tmp'),
    '@kite-res'      => Kite::getPath('resource'),
    '@kite-res-tpl'  => Kite::getPath('resource/templates'),
    '@kite-custom'   => Kite::getPath('custom'),
    // user dirs
    '@kite-u'        => OS::userHomeDir('.kite'),
    '@kite-user'     => OS::userHomeDir('.kite'),
    '@kite-u-tmp'    => OS::userHomeDir('.kite/tmp'),
    '@kite-u-res'    => OS::userHomeDir('.kite/resource'),
    '@kite-u-custom' => OS::userHomeDir('.kite/custom'),
]);

return $loader;
