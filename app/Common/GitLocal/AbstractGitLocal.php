<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitLocal;

use Inhere\Console\IO\Output;
use Inhere\Kite\Helper\GitUtil;
use PhpGit\Info\RemoteInfo;
use PhpGit\Repo;
use RuntimeException;
use Toolkit\Stdlib\Obj;
use function basename;
use function count;
use function explode;
use function strpos;
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
     * @var Repo
     */
    protected $repo;

    /**
     * @var string
     */
    protected string $host = '';

    /**
     * eg. git@gitlab.gongzl.com
     *
     * @var string
     */
    protected string $gitUrl = '';

    /**
     * @var string
     */
    protected string $workDir = '';

    /**
     * @var array
     */
    protected array $config;

    /**
     * @var Output
     */
    protected Output $output;

    /**
     * default remote name
     *
     * @var string
     */
    protected string $remote = 'origin';

    /**
     * @var string
     */
    protected string $mainRemote = 'main';

    /**
     * @var string
     */
    protected string $forkRemote = 'origin';

    /**
     * @var array
     */
    protected array $projects;

    /**
     * @var array
     */
    protected array $remoteInfo = [];

    /**
     * @var string
     */
    protected string $curMainGroup = '';

    /**
     * @var string
     */
    protected string $curForkGroup = '';

    /**
     * @var string
     */
    protected string $curRepo = '';

    /**
     * @var string
     */
    protected string $defaultBranch = 'master';

    /**
     * @var string
     */
    // protected $curBranch = '';

    /**
     * current project name
     *
     * @var string
     */
    protected string $curPjName = '';

    /**
     * @var array
     */
    protected array $curPjInfo = [];

    /**
     * @param Output|null $output
     * @param array       $config
     *
     * @return static
     */
    public static function new(Output $output = null, array $config = []): self
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
     * @return Repo
     */
    public function getRepo(): Repo
    {
        if (!$this->repo) {
            $this->repo = Repo::new($this->workDir);
        }

        return $this->repo;
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

        Obj::init($this, $config);
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
    public function findProjectName(): string
    {
        $pjName = '';

        $info = $this->getRemoteInfo();
        $path = $info->path;

        if ($path) {
            // TODO check host is: github OR gitlab.
            $pjName = $path;

            // try padding some info
            if (isset($this->projects[$path])) {
                if (!isset($this->projects[$path]['forkGroup'])) {
                    $this->projects[$path]['forkGroup'] = $info->group;
                }
                if (!isset($this->projects[$path]['repo'])) {
                    $this->projects[$path]['repo'] = $info->repo;
                }
            }

            $this->output->liteNote('auto parse project info from git remote url');
        }

        if (!$pjName) {
            $dirName = $this->getDirName();

            // try auto parse project name for dirname.
            if (isset($this->projects[$dirName])) {
                $pjName = $dirName;
                $this->output->liteNote('auto parse project name from dirname.');
            }
        }

        return $pjName;
    }

    /**
     * @return string
     */
    public function getCurBranch(): string
    {
        return $this->getRepo()->getCurrentBranch();
    }

    /**
     * @param bool $toMain
     *
     * @return string
     */
    public function getRepoUrl(bool $toMain = false): string
    {
        $group = $toMain ? $this->getGroupName() : $this->getForkGroupName();
        $path  = $group . '/' . $this->curRepo;

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
     * @param string $pjName
     *
     * @return $this
     */
    public function loadProjectInfo(string $pjName = ''): self
    {
        if ($pjName) {
            $this->setCurPjName($pjName);
        }

        $pjName = $this->curPjName;
        $dGroup = $this->getValue('defaultGroup', '');

        // not exist. dynamic add
        if (!isset($this->projects[$pjName])) {
            // throw new RuntimeException("project '{$pjName}' is not found in the projects");
            $info = $this->getRemoteInfo($this->forkRemote);
            if ($info->isInvalid()) {
                throw new RuntimeException("dynamic load project '{$pjName}' fail. not found git remote info");
            }

            // load main remote info
            $mInfo = $this->getRemoteInfo($this->mainRemote);
            if ($mInfo->isValid()) {
                $dGroup = $mInfo->group;
            }

            $this->projects[$pjName] = [
                'dynamic'   => true,
                'forkGroup' => $info->group,
                'repo'      => $info->repo,
            ];

            $dGroup = $dGroup ?: $info->group;
        }

        $defaultInfo = [
            'name'      => $pjName,
            'repo'      => $pjName, // default use project nam as repo name.
            'group'     => $dGroup,
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
     * @param bool   $useGitUrl
     *
     * @return string
     */
    public function parseRepoUrl(string $repo, bool $useGitUrl = false): string
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

            if ($useGitUrl) {
                // eg. git@gitlab.gongzl.com:go-common/gzh.git
                $repoUrl = $this->gitUrl . ':' . $repo . '.git';
            } else {
                // https://github.com/php-toolkit/toolkit.git
                $repoUrl = $this->host . '/' . $repo . '.git';
            }
        }

        return $repoUrl;
    }

    /**
     * @param string $alias
     *
     * @return string
     */
    public function getRealBranchName(string $alias): string
    {
        if (isset($this->config['branchAliases'])) {
            return $this->config['branchAliases'][$alias] ?? $alias;
        }

        return $alias;
    }

    /**
     * @return array
     */
    public function getBranchAliases(): array
    {
        return $this->config['branchAliases'] ?? [];
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
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getValue(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getParam(string $key, mixed $default = null): mixed
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
     * @param string $remote
     *
     * @return RemoteInfo
     */
    public function getRemoteInfo(string $remote = ''): RemoteInfo
    {
        return $this->getRepo()->getRemoteInfo($remote);
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
     * @param string $mainRemote
     */
    public function setMainRemote(string $mainRemote): void
    {
        if ($mainRemote) {
            $this->mainRemote = $mainRemote;
        }
    }

    /**
     * @return string
     */
    public function getForkRemote(): string
    {
        return $this->forkRemote;
    }

    /**
     * @param string $forkRemote
     */
    public function setForkRemote(string $forkRemote): void
    {
        $this->forkRemote = $forkRemote;
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

    /**
     * @return string
     */
    public function getGitUrl(): string
    {
        return $this->gitUrl;
    }

    /**
     * @param string $gitUrl
     */
    public function setGitUrl(string $gitUrl): void
    {
        $this->gitUrl = $gitUrl;
    }

    /**
     * @return string
     */
    public function getDefaultBranch(): string
    {
        return $this->defaultBranch;
    }

    /**
     * @param string $defaultBranch
     */
    public function setDefaultBranch(string $defaultBranch): void
    {
        $this->defaultBranch = $defaultBranch;
    }
}
