<?php declare(strict_types=1);

namespace Inhere\Kite\Common\ProcPool;

/**
 * Class AbstractPool
 *
 * @package Inhere\Kite\ProcPool
 */
abstract class AbstractPool
{
    /**
     * Worker logic handler. eg:
     *
     * function (Swoole\Process\Pool $pool, int $workerId) {
     *      // do something
     * }
     *
     * @var callable
     */
    protected $startHandler;

    /**
     * On worker stop handler. see $startHandler
     *
     * @var callable
     */
    protected $stopHandler;

    /**
     * @param callable $handler
     */
    public function onStart(callable $handler): void
    {
        $this->startHandler = $handler;
    }

    /**
     * @param callable $handler
     */
    public function onStop(callable $handler): void
    {
        $this->stopHandler = $handler;
    }

    abstract public function start(): void;

    /**
     * @return int
     */
    public function getBestWorkerNum(): int
    {
        return (int)ceil(swoole_cpu_num() * 1.5);
    }

    /**
     * @return callable
     */
    public function getStartHandler(): callable
    {
        return $this->startHandler;
    }
}
