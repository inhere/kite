<?php

return [
    'webServe' => [
        'host' => '127.0.0.1:8552',
        // document root
        'root' => 'public'
    ],
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
    // tool command usage docs
    'manDocs' => [
        // if 'lang' not setting, will read from ENV.
        // 'lang'  => 'en',
        'paths' => [
            'root' => BASE_PATH . '/resource/mandocs'
        ],
    ],
    // custom scripts for quick run an command
    'scripts' => require 'scripts.php',
];
