<?php

if (!function_exists('vdump')) {
    /**
     * Dump data like var_dump
     *
     * @param mixed ...$vars
     */
    function vdump(...$vars)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        $line = $trace[0]['line'];
        $pos  = $trace[1]['class'] ?? $trace[0]['file'];

        if ($pos) {
            echo "CALL ON $pos($line):\n";
        }

        echo Toolkit\Stdlib\Php::dumpVars(...$vars), PHP_EOL;
    }
}
