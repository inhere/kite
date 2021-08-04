<?php declare(strict_types=1);

namespace Inhere\Kite\Common\Log;

use Psr\Log\AbstractLogger;

/**
 * Class Logger
 *
 * @package Inhere\Kite\Common\Log
 */
class Logger extends AbstractLogger
{


    /**
     * Logs with an arbitrary level.
     *
     * @param mixed   $level
     * @param string  $message
     * @param mixed[] $context
     *
     * @return void
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, $message, array $context = [])
    {
        // TODO: Implement log() method.
    }
}
