<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitLocal;

use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Helper\GitUtil;
use RuntimeException;
use function basename;
use function count;
use function explode;
use function rtrim;
use function strpos;
use function substr;
use function trim;

/**
 * Class AbstractGit
 *
 * @package Inhere\Kite\Common\Git
 */
abstract class AbstractGitLocal
{
    public const GITHUB_HOST = 'https://github.com';

    /**
     * @var string
     */
    protected $host = '';

    /**
     * @var string
     */
    protected $workDir = '';

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Output
     */
    protected $output;

    /**
     * @var string
     */
    protected $remote = 'origin';

    /**
     * @var string
     */
    protected $mainRemote = 'main';

    /**
     * @var string
     */
    protected $forkRemote = 'origin';

    /**
     * @var array
     */
    protected $projects;

    /**
     * @var array
     */
    protected $remoteInfo = [];

    /**
     * @var string
     */
    protected $curMainGroup = '';

    /**
     * @var string
     */
    protected $curForkGroup = '';

    /**
     * @var string
     */
    protected $curRepo = '';

    /**
     * @var string
     */
    protected $curBranch = '';

    /**
     * current project name
     *
     * @var string
     */
    protected $curPjName = '';

    /**
     * @var array
     */
    protected $curPjInfo = [];

    /**
     * @param Output|null $output
     * @param array       $config
     *
     * @return static
     */
    public static function new(Output $output = null, array $config = [])
    {
        return new static($output, $config);
    }

    /**
     * Class constructor.
     *
     * @param Output|null $output
     * @param array       $config
     */
    public function __construct(Output $output = null, array $config = [])
    {
        if ($output) {
            $this->output = $output;
        }

        $this->init($config);
    }

    /**
     * @param array $config
     */
    protected function init(array $config): void
    {
        if (isset($config['projects'])) {
            $this->projects = $config['projects'];
            unset($config['projects']);
        }

        $this->config = $config;

        if (isset($config['hostUrl'])) {
            $this->setHost($config['hostUrl']);
        }
    }

    /**
     * @return array
     */
    public function clone(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getCurBranch(): string
    {
        if (!$this->curBranch) {
            $this->curBranch = GitUtil::getCurrentBranchName($this->workDir);
        }

        return $this->curBranch;
    }

    /**
     * @param bool $toMain
     *
     * @return string
     */
    public function getRepoUrl(bool $toMain = false): string
    {
        $group = $toMain ? $this->getGroupName() : $this->getForkGroupName();
        $path  = "$group/{$this->curRepo}";

        return $this->host . '/' . $path . '.git';
    }

    /**
     * @return string
     */
    public function getGroupName(): string
    {
        if ($this->curMainGroup) {
            return $this->curMainGroup;
        }

        return $this->getDefaultGroupName();
    }

    /**
     * @return string
     */
    public function getDefaultGroupName(): string
    {
        return $this->getValue('defaultGroup', '');
    }

    /**
     * @return string
     */
    public function getForkGroupName(): string
    {
        if ($this->curForkGroup) {
            return $this->curForkGroup;
        }

        return $this->getDefaultForkGroupName();
    }

    /**
     * @return string
     */
    public function getDefaultForkGroupName(): string
    {
        return $this->getValue('defaultForkGroup', '');
    }

    /**
     * @param string $remote
     *
     * @return $this
     */
    public function setRemote(string $remote): self
    {
        $this->remote = $remote;
        return $this;
    }

    /**
     * @param string $remote
     *
     * @return $this
     */
    public function parseRemote(string $remote = ''): self
    {
        if ($remote) {
            $this->setRemote($remote);
        }

        $str = 'git remote get-url --push ' . $this->remote;
        $url = CmdRunner::new($str, $this->workDir)->do()->getOutput(true);

        // git@gitlab.my.com:group/some-lib.git
        if (strpos($url, 'git@') === 0) {
            if (substr($url, -4) === '.git') {
                $url = substr($url, 4, -4);
            } else {
                $url = substr($url, 4);
            }

            // $url = gitlab.my.com:group/some-lib
            [$host, $path] = explode(':', $url, 2);
            [$group, $repo] = explode('/', $path, 2);

            $this->curRepo = $repo;
            if ($this->remote === $this->getDefaultGroupName()) {
                $this->curMainGroup = '';
            }

            $this->curForkGroup = $group;
            $this->remoteInfo   = [
                'host'  => $host,
                'path'  => $path,
                'url'   => $url,
                'group' => $group,
                'repo'  => $repo,
            ];
        } else { // eg: "https://github.com/ulue/swoft-component.git"
            $info = parse_url($url);
            // add
            $info['url']  = $url;

            $uriPath = $info['path'];
            if (substr($uriPath, -4) === '.git') {
                $uriPath = substr($uriPath, 0, -4);
            }

            $info['path'] = trim($uriPath, '/');

            [$group, $repo] = explode('/', $info['path'], 2);
            $info['group']  = $group;

            // TODO
            // $this->curGroup = $group;
            $this->curRepo = $repo;

            $this->curForkGroup = $group;
            $this->remoteInfo = $info;
        }

        return $this;
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
        if (!$this->curRepo) {
            $this->curRepo = $this->curPjInfo['repo'];
        }

        return $this;
    }


    /**
     * @param string $repo
     *
     * @return string
     */
    public function parseRepoUrl(string $repo): string
    {
        $repoUrl = '';

        $repo = trim($repo, '/ ');
        if (GitUtil::isFullUrl($repo)) { // full url
            $repoUrl = $repo;
        } elseif (strpos($repo, '/') > 0) { // eg: user/repo-name
            $nodes = explode('/', $repo);
            if (count($nodes) > 2) { // invalid
                return $repoUrl;
            }

            // https://github.com/php-toolkit/toolkit.git
            $repoUrl = $this->host . '/' . $repo . '.git';
        }

        return $repoUrl;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getValue(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getWorkDir(): string
    {
        return $this->workDir;
    }

    /**
     * @param string $workDir
     *
     * @return AbstractGitLocal
     */
    public function setWorkDir(string $workDir): self
    {
        $this->workDir = $workDir;
        return $this;
    }

    /**
     * @return string
     */
    public function getDirName(): string
    {
        return basename($this->workDir);
    }

    /**
     * @return array
     */
    public function getRemoteInfo(): array
    {
        return $this->remoteInfo;
    }

    /**
     * @param Output $output
     */
    public function setOutput(Output $output): void
    {
        $this->output = $output;
    }

    /**
     * @return string
     */
    public function getCurRepo(): string
    {
        return $this->curRepo;
    }

    /**
     * @param string $curRepo
     */
    public function setCurRepo(string $curRepo): void
    {
        $this->curRepo = $curRepo;
    }

    /**
     * @return string
     */
    public function getCurForkGroup(): string
    {
        return $this->curForkGroup;
    }

    /**
     * @return string
     */
    public function getCurMainGroup(): string
    {
        return $this->curMainGroup;
    }

    /**
     * @return string
     */
    public function getMainRemote(): string
    {
        return $this->mainRemote;
    }

    /**
     * @return string
     */
    public function getForkRemote(): string
    {
        return $this->forkRemote;
    }

    /**
     * @param string $curBranch
     */
    public function setCurBranch(string $curBranch): void
    {
        $this->curBranch = $curBranch;
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
     * @return self
     */
    public function setCurPjName(string $curPjName): self
    {
        $this->curPjName = $curPjName;
        return $this;
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
     * @return array
     */
    public function getCurPjInfo(): array
    {
        return $this->curPjInfo;
    }
}
