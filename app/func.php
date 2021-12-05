<?php

if (!function_exists('env')) {
    /**
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    function env(string $key, string $default = ''): string
    {
        return Toolkit\Stdlib\OS::getEnvStrVal($key, $default);
    }
}