<?php declare(strict_types=1);

namespace Inhere\Kite\Common;

class GitLab
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $projects;

    /**
     * @param array $config
     *
     * @return $this
     */
    public function new(array $config): self
    {
        return new self($config);
    }

    /**
     * Class constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->projects = $config['projects'] ?? [];

        unset($config['projects']);
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getProjects(): array
    {
        return $this->projects;
    }

    /**
     * @param array $projects
     */
    public function setProjects(array $projects): void
    {
        $this->projects = $projects;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }
}
