<?php declare(strict_types=1);

/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

use Inhere\Console\BuiltIn\PharController;
use Inhere\Kite\Console\CliApplication;

/** @var CliApplication $app */
$app->registerCommands('Inhere\\Kite\\Console\\Command', __DIR__ . '/Command');
$app->registerGroups('Inhere\\Kite\\Console\\Controller', __DIR__ . '/Controller');

// $app->addCommand(Inhere\Console\BuiltIn\DevServerCommand::class);

// internal group
$app->addController(PharController::class);

// load simple commands.
require __DIR__ . '/simple-cmds.php';
