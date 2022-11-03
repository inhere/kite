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

function load_kite(): void
{
    if ($kiteDir = (string)getenv('KITE_PATH')) {
        require $kiteDir. '/app/boot.php';
    }
}
