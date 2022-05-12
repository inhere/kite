<?php
/**
 * @var CliApplication $app
 */

use Inhere\Console\IO\Output;
use Inhere\Kite\Console\CliApplication;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Sys\Sys;

$app->addCommand('which', function (FlagsParser $fs, Output $output) {
    $name = $fs->getArg('binName');
    $path = Sys::findExecutable($name);
    if (!$path) {
        $output->println('Not found');
        return;
    }

    $clean = $fs->getOpt('clean');
    $output->colored($clean ? $path : "Path: $path");
}, [
    'desc'      => 'find bin file path, like system `which`',
    'aliases'   => ['where', 'whereis'],
    'options'   => [
        '--clean' => 'bool;clean output, only output path.'
    ],
    'arguments' => [
        'binName' => 'string;the target bin file name for find;true',
    ],
]);
