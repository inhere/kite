<?php

return [
    'gitignore.stub' => '.gitignore',
    'LICENSE.stub'   => 'LICENSE',
    'readme' => [
        'tpl'    => 'component/README.stub',
        'path'   => 'README.md',
        'render' => true,
    ],
    'component/test-bootstrap.stub' => 'test/bootstrap.php',
    'component/autoload.stub'       => [
        'path'   => 'src/AutoLoader.php',
        'render' => true,
    ],
    'component/composer.json.stub'       => [
        'path'   => 'composer.json',
        'render' => true,
    ],
];
