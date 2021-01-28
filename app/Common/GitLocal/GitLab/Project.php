<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitLocal\GitLab;

use Toolkit\Stdlib\Obj;

/**
 * Class Project
 *
 * @package Inhere\Kite\Common\GitLocal\GitLab
 */
class Project
{
    // '/' => '%2F'
    public const PID_SEP = '%2F';

    /**
     * @var string
     */
    private $name;

    /**
     * The main group
     *
     * @var string
     */
    private $group;

    /**
     * The repository name
     *
     * @var string
     */
    private $repo;

    /**
     * Project Id for main group/repo
     * - int 123
     * - string "group%2Frepo"
     *
     * @var string
     */
    private $mainPid;

    /**
     * @var string
     */
    private $forkGroup;

    /**
     * Project Id for forked-group/repo
     * - int 123
     * - string "group%2Frepo"
     *
     * @var string
     */
    private $forkPid;

    /**
     * 是否是根据当前工作目录信息自动加载的信息
     *
     * @var bool
     */
    private $dynamic = false;

    /**
     * @param array $data
     *
     * @return static
     */
    public static function new(array $data): self
    {
        return new self($data);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name'      => $this->name,
            'group'     => $this->group,
            'repo'      => $this->repo,
            'mainPid'   => $this->mainPid,
            'forkGroup' => $this->forkGroup,
            'forkPid'   => $this->forkPid,
            'dynamic'   => $this->dynamic,
        ];
    }

    /**
     * Class constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        Obj::init($this, $data);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Project
     */
    public function setName(string $name): Project
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @param string $group
     *
     * @return Project
     */
    public function setGroup(string $group): Project
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return string
     */
    public function getRepo(): string
    {
        return $this->repo;
    }

    /**
     * @param string $repo
     *
     * @return Project
     */
    public function setRepo(string $repo): Project
    {
        $this->repo = $repo;
        return $this;
    }

    /**
     * @param string|int $mainPid
     *
     * @return Project
     */
    public function setMainPid($mainPid): Project
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
     * @return Project
     */
    public function setForkGroup(string $forkGroup): Project
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
     * @return Project
     */
    public function setForkPid($forkPid): Project
    {
        $this->forkPid = (string)$forkPid;
        return $this;
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
