<?php declare(strict_types=1);
/**
 * This file is part of PTool.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

$app->registerCommands('Inhere\PTool\\Console\\Command', __DIR__ . '/Command');
$app->registerGroups('Inhere\PTool\\Console\\Group', __DIR__ . '/Group');

// $app->addCommand(\Inhere\Console\BuiltIn\DevServerCommand::class);
$app->addController(\Inhere\Console\BuiltIn\PharController::class);
