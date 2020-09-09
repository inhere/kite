<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitLocal;

use Inhere\Kite\Common\GitLocal\GitLab\Project;
use RuntimeException;
use function array_merge;

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
     * @var array
     */
    private $curPjInfo = [];

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

        if (isset($config['hostUrl'])) {
            $this->setHost($config['hostUrl']);
        }
    }

    /**
     * @param string $pjName
     *
     * @return $this
     */
    public function loadCurPjInfo(string $pjName = ''): self
    {
        if ($pjName) {
            $this->setCurPjName($pjName);
        }

        $pjName = $this->curPjName;
        if (!isset($this->projects[$pjName])) {
            throw new RuntimeException("project '{$pjName}' is not found in the projects");
        }

        $defaultInfo = [
            'name'      => $pjName,
            'repo'      => $pjName, // default use project nam as repo name.
            'group'     => $this->getValue('defaultGroup', ''),
            'forkGroup' => $this->getValue('defaultForkGroup', ''),
        ];

        $this->curPjInfo = array_merge($defaultInfo, $this->projects[$pjName]);
        // set current repo
        $this->curRepo = $this->curPjInfo['repo'];

        return $this;
    }

    /**
     * @return string
     */
    public function findPjName(): string
    {
        $pjName  = '';
        $dirName = $this->getDirName();
        $dirPfx  = $this->getValue('dirPrefix', '');

        // try auto parse project name for dirname.
        if (isset($this->projects[$dirName])) {
            $pjName = $dirName;
            $this->output->liteNote('auto parse project name from dirname.');
        } elseif ($dirPfx && strpos($dirName, $dirPfx) === 0) {
            $tmpName = substr($dirName, strlen($dirPfx));

            if (isset($this->projects[$tmpName])) {
                $pjName = $tmpName;
                $this->output->liteNote('auto parse project name from dirname.');
            }
        } else {
            $info = $this->parseRemote()->getRemoteInfo();
            $path = $info['path'] ?? '';

            if ($path && isset($this->projects[$path])) {
                $pjName = $path;

                // try padding some info
                [$forkGroup, $repo] = explode('/', $path, 2);
                if (!isset($this->projects[$path]['forkGroup'])) {
                    $this->projects[$path]['forkGroup'] = $forkGroup;
                }
                if (!isset($this->projects[$path]['repo'])) {
                    $this->projects[$path]['repo'] = $repo;
                }

                $this->output->liteNote('auto parse project name from git remote url');
            }
        }

        return $pjName;
    }

    public function createPRLink(string $srcBranch, string $dstBranch, bool $direct = false): string
    {
        return '';
    }

    /**
     * @param string $pjName
     *
     * @return bool
     */
    public function hasProject(string $pjName): bool
    {
        return isset($this->projects[$pjName]);
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
     *
     * @return GitLab
     */
    public function setCurPjName(string $curPjName): self
    {
        $this->curPjName = $curPjName;
        return $this;
    }

    /**
     * @return array
     */
    public function getCurPjInfo(): array
    {
        return $this->curPjInfo;
    }

    /**
     * @return Project
     */
    public function getCurProject(): Project
    {
        return Project::new($this->curPjInfo);
    }
}
