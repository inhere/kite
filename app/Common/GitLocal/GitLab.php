<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitLocal;

/**
 * Class GitLab
 *
 * @package Inhere\Kite\Common\Git
 */
class GitLab extends AbstractGitLocal
{
    /**
     * @var array
     */
    private $projects;

    /**
     * Class constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct();
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

}
