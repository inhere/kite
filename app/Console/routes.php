<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

use Inhere\Kite\Console\Application;

/** @var Application $app */
$app->registerCommands('Inhere\Kite\\Console\\Command', __DIR__ . '/Command');
$app->registerGroups('Inhere\Kite\\Console\\Group', __DIR__ . '/Group');

// $app->addCommand(\Inhere\Console\BuiltIn\DevServerCommand::class);
$app->addController(\Inhere\Console\BuiltIn\PharController::class);
