<?php

define('BASE_PATH', dirname(__DIR__));

/** @var Composer\Autoload\ClassLoader $loader */
$loader = require dirname(__DIR__) . '/vendor/autoload.php';

$loader->addPsr4("Toolkit\\PFlag\\", __DIR__ . '/toolkit/pflag/src/');
$loader->addPsr4("Symfony\\Component\\Yaml\\", __DIR__ . '/symfony/yaml/');

return $loader;