<?php declare(strict_types=1);

namespace Inhere\Kite\Common\ProcPool;

use RuntimeException;
use Swoole\Process;

/**
 * Class ProcessPool2
 *
 * @package Inhere\Kite\ProcPool
 */
class ProcessPool2 extends AbstractPool
{
    /**
     * @var int
     */
    private $workerId = 0;

    /**
     * @var array [pid => wid]
     */
    private $pidMap = [];

    /**
     * @var int
     */
    private $workerNum;

    /**
     * @var bool
     */
    private $coroutine;

    /**
     * @var bool
     */
    private $redirectIO;

    /**
     * @link https://wiki.swoole.com/wiki/page/289.html
     * @var int
     */
    private $msgQueueKey;

    /**
     * @var bool
     */
    private $keepalive = false;

    /**
     * @var bool
     */
    private $blockWait = true;

    /**
     * @var Process[]
     */
    private $workers;

    /**
     * @param int  $workerNum
     * @param int  $msgQueueKey
     * @param bool $redirectIO
     * @param bool $enableCoroutine
     *
     * @return static
     */
    public static function new(
        int $workerNum,
        int $msgQueueKey = 0,
        bool $redirectIO = false,
        bool $enableCoroutine = true
    ): self {
        return new self($workerNum, $msgQueueKey, $redirectIO, $enableCoroutine);
    }

    /**
     * Class constructor.
     * doc see https://wiki.swoole.com/wiki/page/214.html
     *
     * @param int  $workerNum
     * @param int  $msgQueueKey
     * @param bool $redirectIO
     * @param bool $enableCoroutine
     */
    public function __construct(
        int $workerNum,
        int $msgQueueKey = 0,
        bool $redirectIO = false,
        bool $enableCoroutine = true
    ) {
        $this->workerNum   = $workerNum;
        $this->msgQueueKey = $msgQueueKey;
        $this->redirectIO  = $redirectIO;
        $this->coroutine   = $enableCoroutine;
    }

    public function start(): void
    {
        if (!$fn = $this->startHandler) {
            throw new RuntimeException('the worker start handler is required before start');
        }

        // create and start
        for ($i = 0; $i < $this->workerNum; $i++) {
            $this->createProcess($i, $fn);
        }

        // waiting
        if ($this->blockWait) {
            while ($ret = Process::wait()) {
                $this->handleExit($ret, $fn);
            }
        } else {
            // SIGCHLD = 17
            Process::signal(17, function () use ($fn) {
                // 必须为false，非阻塞模式
                while ($ret = Process::wait(false)) {
                    $this->handleExit($ret, $fn);
                }
            });
        }

        // Event::wait();
    }

    /**
     * @param array    $ret
     * @param callable $fn
     */
    protected function handleExit(array $ret, callable $fn): void
    {
        $wid = $this->workerId;

        // on stop
        if ($stopFunc = $this->stopHandler) {
            $stopFunc($this, $wid, $ret);
        }

        if ($this->keepalive) {
            $this->createProcess($wid, $fn);
        }
    }

    /**
     * @param int      $wid
     * @param callable $fn
     */
    protected function createProcess(int $wid, callable $fn): void
    {
        $proc = new Process(function (Process $proc) use ($fn, $wid) {
            $this->workerId = $wid;
            // on start
            $fn($this, $wid);
        }, $this->redirectIO, 0, $this->coroutine);

        if ($this->msgQueueKey) {
            $proc->useQueue($this->msgQueueKey);
        }

        $proc->start();

        $this->workers[$wid] = $proc;
        $this->pidMap[$wid]  = $proc->pid;
    }

    /**
     * @param int $workerId
     *
     * @return Process
     */
    public function getProcess(int $workerId = -1): Process
    {
        // return current worker
        if ($workerId < 0) {
            return $this->workers[$this->workerId];
        }

        return $this->workers[$workerId];
    }

    /**
     * @return int
     */
    public function getWorkerId(): int
    {
        return $this->workerId;
    }

    /**
     * @return bool
     */
    public function isBlockWait(): bool
    {
        return $this->blockWait;
    }

    /**
     * @param bool $blockWait
     */
    public function setBlockWait(bool $blockWait): void
    {
        $this->blockWait = $blockWait;
    }

    /**
     * @return bool
     */
    public function isKeepalive(): bool
    {
        return $this->keepalive;
    }

    /**
     * @param bool $keepalive
     */
    public function setKeepalive(bool $keepalive): void
    {
        $this->keepalive = $keepalive;
    }
}
