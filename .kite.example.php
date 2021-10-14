<?php
/**
 * you can copy the file as `.kite.php` for custom config kite.
 */

use Toolkit\Stdlib\OS;

$osName = OS::name();

return [
    // application config
    'no-interactive' => false,
    'php:serve' => [
        'host' => '127.0.0.1:8552',
        // document root
        'root' => 'public'
    ],
    'logger' => [
        'logfile' => BASE_PATH . '/tmp/logs/kite.log',
    ],
    'jumper'         => [
        'datafile' => __DIR__ . "/tmp/jump-data.$osName.json",
        'aliases'  => [
            'home'  => '~',
            'godev' => '~/Workspace/godev',
            'php'   => '~/Workspace/php',
        ],
    ],
    /** @see \Inhere\Kite\Console\Component\AutoSetProxyEnv */
    'autoProxy'      => [
        'envSettings' => [
            // proxy settings
            // export http_proxy=http://127.0.0.1:1081;export https_proxy=http://127.0.0.1:1081;
            // 'http_proxy'  => 'http://127.0.0.1:1081',
            // 'https_proxy' => 'http://127.0.0.1:1081',
        ],
        'groupLimits' => [],
        'commandIds'  => [
            // item is commandID
            // 'php:ghPkg',
        ],
    ],
    'git' => [
        // 'auto-sign' => true,
        // 'sign-text' => 'inhere <in.798@qq.com>',
    ],
    /** @see Inhere\Kite\Common\GitLocal\GitLab */
    'gitlab'  => [
        'projects' => [

        ],
    ],
    // tool command usage docs
    'manDocs' => [
        // if 'lang' not setting, will read from ENV.
        // 'lang'  => 'en',
        'paths' => [
            'root' => BASE_PATH . '/resource/mandocs'
        ],
    ],
    // command aliases. element is: alias command => real command
    'aliases' => [],
    // custom scripts
    'scripts' => [
        'echo'          => 'echo hi',
        'envsearch'     => 'env | grep $1',
        'test'          => [
            'echo $SHELL',
            'echo hello'
        ],
        // git quick use
        'gst'           => 'git status',
        'st'            => 'git status',
        'co'            => 'git checkout $@',
        'br'            => 'git branch $?',
        'pul'           => 'git pul $?',
        'pull'          => 'git pull $?',
        // golang usage
        'gofmt'         => 'go fmt ./...',
        'gotest'        => 'go test ./...',
        // php tool usage
        'csfix'         => 'php-cs-fixer fix $1',
        'csfix-and-git' => [
            'php-cs-fixer fix $1',
            'git add . && git commit -m "run php-cs-fixer for the $1"'
        ],
        'phpcs'         => 'php-cs-fixer fix',
        // docker
        'dcnotag'       => [
            '_meta' => [
                'desc' => 'display all no-tags docker images',
            ],
            'docker images -f "dangling=true"'
        ],
        'dcclrnotag'    => [
            '_meta' => [
                'desc' => 'clear all no-tags docker images',
            ],
            'docker rmi $(docker images -f "dangling=true" -q)'
        ],
    ],
];
