<?php declare(strict_types=1);

use Toolkit\Stdlib\OS;

$basePath = Inhere\Kite\Kite::basePath();

return [
    'app'           => [
        'debug'    => true,
        'rootPath' => BASE_PATH,
    ],
    'logger'        => [
        'name'    => 'Kite',
        'logfile' => OS::userCacheDir('kite.log'),
    ],
    'git'           => [
        // 'auto-sign' => true,
        // 'sign-text' => 'inhere <in.798@qq.com>',

        // ----- gitflow ----

        // remote
        'mainRemote' => 'main',
        'forkRemote' => 'origin',
    ],
    'gitlab'        => [
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
        'baseApi'          => '',
    ],
    'github'        => [
        // remote
        'mainRemote'       => 'main',
        'forkRemote'       => 'origin',
        // group
        'defaultGroup'     => 'swoft',
        'defaultForkGroup' => 'ulue',
        'defaultBranch'   => 'main',
        'redirectGit'      => [
            '*'
        ],
        // github api config
        // 'baseApi' => '',
    ],
    'osEnv'         => [
        // env settings
    ],
    'osPathEnv'     => [
        // os path env settings
        // '/path/to/my-tool/bin',
    ],
    'proxyEnv'      => [
        // proxy settings
        // 'http_proxy'  => 'http://127.0.0.1:1081',
        // 'https_proxy' => 'http://127.0.0.1:1081',
    ],
    'jenkins' => [
        'hostUrl'  => env('JK_HOST_URL'),
        'username' => env('JK_UNAME'),
        'password' => env('JK_PASSWD'),
        'apiToken' => env('JK_API_TOKEN'),
    ],
    // tool command usage docs
    'manDocs'       => [
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
    'scriptRunner'  => [
        'enable' => true,
    ],
    'scriptDirs'    => [
        // BASE_PATH . '/script',
        $basePath . '/script',
        $basePath . '/custom/script',
    ],
    // custom scripts for quick run an command
    'scripts'       => require 'scripts.php',
    // command aliases. element is: alias command => real command
    'aliases'       => require 'aliases.php',
    // custom tools management
    'toolManager'    => [
        'workdir' => '',
    ],
    'tools'   => require 'tools.php',
];
