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
use Inhere\Kite\Kite;

/** @var CliApplication $app */
$app->registerCommands('Inhere\\Kite\\Console\\Command', __DIR__ . '/Command');
$app->registerGroups('Inhere\\Kite\\Console\\Controller', __DIR__ . '/Controller');

// $app->addCommand(Inhere\Console\BuiltIn\DevServerCommand::class);

// internal group
$app->addController(PharController::class);

// load custom commands
if ($commands = Kite::config()->getArray('cliCommands')) {
    $app->addCommands($commands);
}

// load custom controllers
if ($controllers = Kite::config()->getArray('cliControllers')) {
    $app->addControllers($controllers);
}

// load simple commands.
require __DIR__ . '/simple-cmds.php';
