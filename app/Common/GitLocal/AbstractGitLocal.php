<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitLocal;

use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Helper\GitUtil;
use function basename;
use function count;
use function explode;
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
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function clone(): array
    {
        return [];
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
        } else {
            $info = parse_url($url);
            // add
            $info['url'] = $url;

            // TODO
            // $this->curGroup = $group;
            // $this->curRepo  = $repo;

            $this->remoteInfo = $info;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getRemoteInfo(): array
    {
        return $this->remoteInfo;
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
}
