<?php

return [
    'gitlab' => [
        // remote
        'mainRemote'       => 'main',
        'forkRemote'       => 'origin',
        // group
        'defaultGroup'     => 'wzl',
        'defaultForkGroup' => 'inhere',
    ],
    'github' => [
        // remote
        'mainRemote'       => 'main',
        'forkRemote'       => 'origin',
        // group
        'defaultGroup'     => 'swoft',
        'defaultForkGroup' => 'ulue',
    ],
    // command aliases. element is: alias command => real command
    'aliases' => [
        'acp'    => 'git:acp',
        'glpr'   => 'gitlab:pr',
        'config' => 'self config',
    ],
    // custom scripts for quick run an command
    'scripts' => require 'scripts.php',
];
