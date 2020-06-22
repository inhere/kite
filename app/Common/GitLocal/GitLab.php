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
     * @var string
     */
    private $curPjName = '';

    /**
     * @var string
     */
    private $curBranch = '';

    /**
     * @var string
     */
    private $srcBranch = '';

    /**
     * @var string
     */
    private $dstBranch = '';

    /**
     * Class constructor.
     *
     * @param array $config
     */
    protected function init(array $config): void
    {
        $this->projects = $config['projects'] ?? [];

        unset($config['projects']);
        $this->config = $config;
    }

    public function createPRLink(string $srcBranch, string $dstBranch, bool $direct = false): string
    {
        return '';
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
     * @return string
     */
    public function getCurPjName(): string
    {
        return $this->curPjName;
    }

    /**
     * @param string $curPjName
     */
    public function setCurPjName(string $curPjName): void
    {
        $this->curPjName = $curPjName;
    }
}
