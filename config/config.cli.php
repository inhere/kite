<?php

use Inhere\Kite\Helper\AppHelper;
use Toolkit\Stdlib\OS;

$osName = OS::name();

return [
    // enable interactive
    'no-interactive' => false,
    /** @see \Inhere\Kite\Lib\Jump\QuickJump */
    'jumper' => [
        'datafile' => OS::userHomeDir(".kite/tmp/kite-jump.$osName.json"),
        'aliases' => [
            'home' => '~',
        ]
    ],
    // self:webui
    'webui' => [
        'addr' => '127.0.0.1:8090',
        'root' => BASE_PATH . '/public',
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
];
