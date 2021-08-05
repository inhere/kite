<?php

return [
    'app'  => [
        'staticDir' => '/static',
    ],
    'staticDir' => [
        '/static' => BASE_PATH . '/pub'
    ],
    'git' => [
        // remote
        'mainRemote'       => 'main',
        'forkRemote'       => 'origin',
    ],
    'gitlab'  => [
        // remote
        'mainRemote'       => 'main',
        'forkRemote'       => 'origin',
        // group
        'defaultGroup'     => 'group',
        'defaultForkGroup' => 'inhere',
        'redirectGit'   => [
            'ac',
            'acp',
            'log',
            'info',
            'push',
            'fetch',
            'update',
            'tagNew',
            'tagList',
            'tagDelete',
            'changelog',
        ],
    ],
    'github'  => [
        // remote
        'mainRemote'       => 'main',
        'forkRemote'       => 'origin',
        // group
        'defaultGroup'     => 'swoft',
        'defaultForkGroup' => 'ulue',
        'loadEnvOn'     => [],
        'redirectGit'   => [
            'acp',
            'log',
            'info',
            'push',
            'fetch',
            'update',
            'tagNew',
            'tagList',
            'tagDelete',
            'changelog',
        ],
    ],
    'osEnv'   => [
        // proxy settings
        // 'http_proxy'  => 'http://127.0.0.1:1081',
        // 'https_proxy' => 'http://127.0.0.1:1081',
    ],
    // command aliases. element is: alias command => real command
    'aliases' => [
        'ac'     => 'git:ac',
        'acp'    => 'git:acp',
        'glpr'   => 'gitlab:pr',
        'config' => 'self config',
        'webui'  => 'self webui',
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

    'pluginDirs' => [
        // '/plugin/'
    ],
];
