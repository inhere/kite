<?php

use Toolkit\Stdlib\OS;

$basePath = Inhere\Kite\Kite::basePath();

return [
    'app'        => [
    ],
    'logger'     => [
        'name'    => 'Kite',
        'logfile' => OS::userCacheDir('kite.log'),
    ],
    'git'        => [
        // remote
        'mainRemote' => 'main',
        'forkRemote' => 'origin',
        // 'auto-sign' => true,
        // 'sign-text' => 'inhere <in.798@qq.com>',
    ],
    'gitlab'     => [
        // remote
        'mainRemote'       => 'main',
        'forkRemote'       => 'origin',
        // group
        'defaultGroup'     => 'group',
        'defaultForkGroup' => 'inhere',
        'redirectGit'      => [
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
        // gitlab api config
        'baseUrl'          => '',
    ],
    'github'     => [
        // remote
        'mainRemote'       => 'main',
        'forkRemote'       => 'origin',
        // group
        'defaultGroup'     => 'swoft',
        'defaultForkGroup' => 'ulue',
        'redirectGit'      => [
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
        // github api config
        // 'baseUrl' => '',
    ],
    'osEnv'      => [
        // proxy settings
        // 'http_proxy'  => 'http://127.0.0.1:1081',
        // 'https_proxy' => 'http://127.0.0.1:1081',
    ],
    // tool command usage docs
    'manDocs'    => [
        // if 'lang' not setting, will read from ENV.
        // 'lang'  => 'en',
        'fallbackLang' => 'en',
        'paths'        => [
            'root' => $basePath . '/resource/mandocs'
        ],
    ],
    'pluginDirs' => [
        // BASE_PATH . '/plugin'
        $basePath . '/plugin',
        $basePath . '/custom/plugin',
    ],
    'scriptDirs' => [
        // BASE_PATH . '/script',
        $basePath . '/script',
        $basePath . '/custom/script',
    ],
    // command aliases. element is: alias command => real command
    'aliases'    => require 'aliases.php',
    // custom scripts for quick run an command
    'scripts'    => require 'scripts.php',
];
