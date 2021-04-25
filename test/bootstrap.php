<?php declare(strict_types=1);
/**
 * This file is part of toolkit/cli-utils.
 *
 * @link     https://github.com/php-toolkit/cli-utils
 * @author   https://github.com/inhere
 * @license  MIT
 */

error_reporting(E_ALL);
ini_set('display_errors', 'On');
date_default_timezone_set('Asia/Shanghai');

define('BASE_PATH', dirname(__DIR__));

/** @var Composer\Autoload\ClassLoader $loader */
$loader = require dirname(__DIR__) . '/vendor/autoload.php';
$loader->setPsr4("Inhere\\KiteTest\\", __DIR__ . '/unittest/');
