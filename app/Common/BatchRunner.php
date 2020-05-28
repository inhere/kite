<?php declare(strict_types=1);

namespace Inhere\Kite\Common;

/**
 * Class BatchExec
 *
 * @package Inhere\Kite\Common
 */
class BatchRunner
{
    /**
     * Ignore check prevision return code
     *
     * @var bool
     */
    private $ignoreCode = false;

    /**
     * [
     *  'echo hi',
     *  'do something'
     * ]
     *
     * @var array
     */
    private $commands;

    /**
     * Class constructor.
     *
     * @param array $commands
     */
    public function __construct(array $commands)
    {
        $this->commands = $commands;
    }

    /**
     * @param bool $ignoreCode
     *
     * @return BatchRunner
     */
    public function setIgnoreCode(bool $ignoreCode): BatchRunner
    {
        $this->ignoreCode = $ignoreCode;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIgnoreCode(): bool
    {
        return $this->ignoreCode;
    }

}
