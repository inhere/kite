<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitLocal;

/**
 * Class GitLocal
 *
 * @package Inhere\Kite\Common\GitLocal
 */
class GitLocal extends AbstractGitLocal
{
    /**
     * Error code
     *
     * @var integer
     */
    private $errCode = 0;

    /**
     * Error message
     *
     * @var string
     */
    private $errMsg = '';

    public function findLastTag(): string
    {
        return '';
    }

    /**
     * Get the value of errCode
     *
     * @return int
     */ 
    public function getErrCode(): int
    {
        return $this->errCode;
    }

    /**
     * Get error message
     *
     * @return string
     */ 
    public function getErrMsg(): string
    {
        return $this->errMsg;
    }
}
