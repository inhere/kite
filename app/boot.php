<?php

use Inhere\Kite\Kite;
use Toolkit\Stdlib\OS;

define('BASE_PATH', $baseDir = dirname(__DIR__));
defined('IN_PHAR') || define('IN_PHAR', false);

/** @var Composer\Autoload\ClassLoader $loader */
$loader = require $baseDir . '/vendor/autoload.php';

$loader->addPsr4("PhpPkg\\EasyTpl\\", $baseDir . '/vendor/phppkg/easytpl/src/');
$loader->addPsr4("PhpPkg\\Config\\", $baseDir . '/vendor/phppkg/config/src/');
$loader->addPsr4("PhpPkg\\Ini\\", $baseDir . '/vendor/phppkg/ini/src/');
$loader->addPsr4("Custom\\Lib\\", $baseDir . '/custom/lib/');
$loader->addPsr4("Custom\\Console\\", $baseDir . '/custom/console/');

// save loader for plugin manager
// Kite::$loader = $loader;
$kiteUserDir = OS::userHomeDir('.kite');

// user kite dirs
Kite::setAliases([
    '@user-kite'    => $kiteUserDir,
    '@user-tmp'     => $kiteUserDir . '/tmp',
    '@user-res'     => $kiteUserDir . '/resource',
    '@user-custom'  => $kiteUserDir . '/custom',
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


// do something ...

return $loader;
