<?php

return [
    'webServe'  => [
        'host'  => '127.0.0.1:8552',
        // document root
        'root' => 'public'
    ],

    'gitlab'  => [
        'projects' => [

        ],
    ],
    // tool command usage docs
    'manDocs' => [
        // if 'lang' not setting, will read from ENV.
        // 'lang'  => 'en',
        'paths' => [
            'root' => BASE_PATH . '/docs'
        ],
    ],
    // custom scripts
    'scripts' => [
        'echo' => 'echo hi',
        'test' => [
            'echo $SHELL',
            'echo hello'
        ],
    ],
];
