<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitLocal\GitLab;

use Inhere\Kite\Common\GitLocal\GitProject;

/**
 * Class Project
 *
 * @package Inhere\Kite\Common\GitLocal\GitLab
 */
class GlProject extends GitProject
{
    // '/' => '%2F'
    public const PID_SEP = '%2F';

    /**
     * Project Id for main group/repo
     * - int 123
     * - string "group%2Frepo"
     *
     * @var string
     */
    private $mainPid;

    /**
     * Project Id for forked-group/repo
     * - int 123
     * - string "group%2Frepo"
     *
     * @var string
     */
    private $forkPid;

    /**
     * @param string|int $mainPid
     *
     * @return GlProject
     */
    public function setMainPid($mainPid): GlProject
    {
        $this->mainPid = (string)$mainPid;
        return $this;
    }

    /**
     * @return string
     */
    public function getMainPid(): string
    {
        if (!$this->mainPid) {
            // string pid: group + %2F + repo
            $this->mainPid = $this->group . self::PID_SEP . $this->repo;
        }

        return $this->mainPid;
    }

    /**
     * @return string
     */
    public function getForkGroup(): string
    {
        return $this->forkGroup;
    }

    /**
     * @param string $forkGroup
     *
     * @return GlProject
     */
    public function setForkGroup(string $forkGroup): GlProject
    {
        $this->forkGroup = $forkGroup;
        return $this;
    }

    /**
     * @return string
     */
    public function getForkPid(): string
    {
        if (!$this->forkPid) {
            // string pid: group + %2F + repo
            $this->forkPid = $this->forkGroup . self::PID_SEP . $this->repo;
        }

        return $this->forkPid;
    }

    /**
     * @param string|int $forkPid
     *
     * @return GlProject
     */
    public function setForkPid($forkPid): GlProject
    {
        $this->forkPid = (string)$forkPid;
        return $this;
    }

}
