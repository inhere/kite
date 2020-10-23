<?php

function vdump(...$args)
{
    echo Toolkit\Stdlib\Php::dumpVars(...$args), PHP_EOL;
}
