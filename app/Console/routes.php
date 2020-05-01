<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/11/27
 * Time: 下午10:58
 *
 * @var Inhere\PTool\Console\Application $app
 */

$app->registerCommands('Inhere\PTool\\Console\\Command', __DIR__ . '/Command');
$app->registerGroups('Inhere\PTool\\Console\\Group', __DIR__ . '/Group');

$app->addCommand(\Inhere\Console\BuiltIn\DevServerCommand::class);
$app->addController(\Inhere\Console\BuiltIn\PharController::class);
