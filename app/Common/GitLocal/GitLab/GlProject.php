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
    private string $mainPid = '';

    /**
     * Project Id for forked-group/repo
     * - int 123
     * - string "group%2Frepo"
     *
     * @var string
     */
    private string $forkPid = '';

    /**
     * @param int|string $mainPid
     *
     * @return GlProject
     */
    public function setMainPid(int|string $mainPid): GlProject
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
        return $this->forkGroup ?: $this->group;
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
            $this->forkPid = $this->getForkGroup() . self::PID_SEP . $this->repo;
        }

        return $this->forkPid;
    }

    /**
     * @param int|string $forkPid
     *
     * @return GlProject
     */
    public function setForkPid(int|string $forkPid): GlProject
    {
        $this->forkPid = (string)$forkPid;
        return $this;
    }

}
