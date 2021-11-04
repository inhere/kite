<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitLocal;

use Toolkit\Stdlib\Obj\AbstractMap;

/**
 * Class GhProject
 *
 * @package Inhere\Kite\Common\GitLocal
 */
class GitProject extends AbstractMap
{
    /**
     * The project name
     *
     * @var string
     */
    public string $name;

    /**
     * The main group
     *
     * @var string
     */
    public string $group;

    /**
     * The repository name
     *
     * @var string
     */
    public string $repo;

    /**
     * The forked group
     *
     * @var string
     */
    public string $forkGroup;

    /**
     * 是否是根据当前工作目录信息自动加载的信息
     *
     * @var bool
     */
    private bool $dynamic = false;

    /**
     * @param bool $forked
     *
     * @return string
     */
    public function getPath(bool $forked = false): string
    {
        return ($forked ? $this->forkGroup : $this->group) . '/' . $this->repo;
    }

    /**
     * @return bool
     */
    public function isDynamic(): bool
    {
        return $this->dynamic;
    }

    /**
     * @param bool $dynamic
     */
    public function setDynamic(bool $dynamic): void
    {
        $this->dynamic = $dynamic;
    }
}
