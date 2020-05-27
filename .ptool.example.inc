<?php

return [
    'gitlab'  => [
        'projects' => [

        ],
    ],
    // tool command usage docs
    'manDocs' => [
        'lang'  => 'en',
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
