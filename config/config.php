<?php

return [
    'app'  => [
        'staticDir' => '/static',
    ],
    'webui' => [
        'addr' => '127.0.0.1:8090',
        'root' => BASE_PATH . '/public',
    ],
    'staticDir' => [
        '/static' => BASE_PATH . '/pub'
    ],
    // view renderer
    'renderer'  => [
        'viewsDir' => BASE_PATH . '/resource/views',
        'globalVars' => [
            '_staticPath' => '/static'
        ],
    ],
    // @see app/Console/Controller/PhpController.php
    'php:serve' => [
        'hce-file' => 'test/clienttest/http-client.env.json',
        'hce-env'  => getenv('APP_ENV') ?: 'development',
        // 'entry'     => 'public/index.php',
        // document root
        'root'     => 'public',
        'entry'    => 'public/index.php',
        // 'php-bin'  => 'php'
        // 'addr' => '127.0.0.1:8552',
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
