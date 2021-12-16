<?php

$line = 'hw.ncpu: 8';

preg_match('/hw.ncpu: (\d+)/', $line, $matches);

var_dump($matches, $matches[1]);
