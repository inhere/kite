<?php declare(strict_types=1);

namespace Inhere\Kite\Http;

/**
 * Class ErrorHandler
 *
 * @package Inhere\Kite\Http
 */
class ErrorHandler
{
    public function run(\Throwable $e): void
    {
        echo 'ERROR';
    }
}
