<?php

$d = ['foo' => 'bar', 'baz' => 'long', 'emptySub' => []];

echo "Associative array always output as object: ", json_encode($d), "\n";
echo "Associative array always output as object: ", json_encode($d, JSON_FORCE_OBJECT), "\n\n";
