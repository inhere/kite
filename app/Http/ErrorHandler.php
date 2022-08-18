<?php declare(strict_types=1);

namespace Inhere\Kite\Http;

use Throwable;
use Toolkit\Stdlib\Php;

/**
 * Class ErrorHandler
 *
 * @package Inhere\Kite\Http
 */
class ErrorHandler
{
    /**
     * @var bool
     */
    private bool $debug;

    /**
     * Class constructor.
     *
     * @param bool $debug
     */
    public function __construct(bool $debug = false)
    {
        $this->debug = $debug;
    }

    /**
     * @param Throwable $e
     */
    public function run(Throwable $e): void
    {
        // echo 'System Error!';
        echo Php::exception2html($e, $this->debug);
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }
}
