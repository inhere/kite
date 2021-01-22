<?php

// custom scripts for quick run an command
return  [
    'echo' => 'echo hi',
    'test' => [
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
];
