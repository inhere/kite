<?php

// custom scripts for quick run an command
return [
    'echo'     => 'echo hi',
    'test'     => [
        'echo $SHELL',
        'echo hello'
    ],
    // git quick use
    'gst'      => 'git status',
    'st'       => 'git status',
    'co'       => 'git checkout $@',
    'br'       => 'git branch $?',
    'pul'      => 'git pul $?',
    'pull'     => 'git pull $?',
    'local-ip' => "ifconfig | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'",
];
