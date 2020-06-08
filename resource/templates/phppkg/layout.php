<?php

return [
    // options
    '__options'      => [
        // eg: ['author' => 'inhere', ]
        // usage: {{author}}
        'tplVars' => [],
        'tplPath' => __DIR__
    ],
    // templates
    'gitignore'      => '.gitignore',
    'php_cs'         => '.php_cs',
    'license'        => [
        'tpl'  => 'mit.LICENSE',
        'path' => 'LICENSE',
    ],
    'readme'         => [
        'tpl'    => 'component/README.stub',
        'path'   => 'README.md',
        'render' => true,
    ],
    'test/bootstrap' => 'test/bootstrap.php',
    'composer.json'  => [
        'path'   => 'composer.json',
        'render' => true,
    ],
];
