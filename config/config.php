<?php

use Toolkit\Stdlib\OS;

return [
    'app'  => [
        'staticDir' => '/static',
    ],
    'logger'  => [
        'name'    => 'Kite',
        'logfile' => OS::userCacheDir('kite.log'),
    ],
    'staticDir' => [
        '/static' => BASE_PATH . '/public'
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
    // tool command usage docs
    'manDocs' => [
        // if 'lang' not setting, will read from ENV.
        // 'lang'  => 'en',
        'fallbackLang'  => 'en',
        'paths'    => [
            'root' => BASE_PATH . '/resource/mandocs'
        ],
    ],
    'pluginDirs' => [
        // '/plugin/'
    ],
    // command aliases. element is: alias command => real command
    'aliases' => require 'aliases.php',
    // custom scripts for quick run an command
    'scripts' => require 'scripts.php',
];
