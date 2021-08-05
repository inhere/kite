<?php declare(strict_types=1);

namespace Inhere\Kite\Http;

use Throwable;

/**
 * Class ErrorHandler
 *
 * @package Inhere\Kite\Http
 */
class ErrorHandler
{
    /**
     * @param Throwable $e
     */
    public function run(Throwable $e): void
    {
        echo 'ERROR';
    }
}
