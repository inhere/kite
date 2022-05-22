<?php

return [
    'act'         => [
        'desc'      => 'Run your GitHub Actions locally 🚀',
        'deps'   => 'brew', // all(install, update) required brew
        // 'workdir' => '/var/tmp',
        'homepage'  => 'https://github.com/nektos/act',
        'afterTips' => [
            'install' => 'tool has been installed to the OS'
        ],
        'install'   => [
            // run, command, script
            'run'  => 'brew install act',
            // 'deps' => 'brew', // install required brew
        ],
        'update'    => [
            'run' => 'brew upgrade act',
        ],
    ],
];
