<?php

return [
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