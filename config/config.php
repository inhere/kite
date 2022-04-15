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
    'gitflow'      => [
        // remote
        'mainRemote' => 'main',
        'forkRemote' => 'origin',
    ],
    'gitlab'     => [
        // remote
        'mainRemote'       => 'main',
        'forkRemote'       => 'origin',
        // group
        'defaultGroup'     => 'group',
        'defaultForkGroup' => 'inhere',
        'redirectGit'      => [
            '*'
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
           '*'
        ],
        // github api config
        // 'baseUrl' => '',
    ],
    'osEnv'      => [
        // env settings
    ],
    'osPathEnv'      => [
        // os path env settings
        // '/path/to/my-tool/bin',
    ],
    'proxyEnv'      => [
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
    /** @see \Inhere\Kite\Console\Plugin\PluginManager */
    'pluginManager' => [
        'enable'     => true,
        'pluginDirs' => [
            // BASE_PATH . '/plugin'
            $basePath . '/plugin',
            $basePath . '/custom/plugin',
            // dir => namespace. TODO
            // $basePath . '/custom/plugin' => "Custom\\Plugin\\",
        ],
    ],
    /** @see \Inhere\Kite\Component\ScriptRunner */
    'scriptRunner' => [
        'enable'  => true,
    ],
    'scriptDirs' => [
        // BASE_PATH . '/script',
        $basePath . '/script',
        $basePath . '/custom/script',
    ],
    // custom scripts for quick run an command
    'scripts'    => require 'scripts.php',
    // command aliases. element is: alias command => real command
    'aliases'    => require 'aliases.php',
];
