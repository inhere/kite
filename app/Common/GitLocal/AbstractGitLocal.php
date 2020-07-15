<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitLocal;

use Inhere\Console\IO\Output;
use Inhere\Kite\Helper\GitUtil;
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
     * @param Output $output
     * @param array  $config
     *
     * @return static
     */
    public static function new(Output $output, array $config = [])
    {
        return new static($output, $config);
    }

    /**
     * Class constructor.
     *
     * @param Output $output
     * @param array  $config
     */
    public function __construct(Output $output, array $config = [])
    {
        $this->output = $output;

        $this->init($config);
    }

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
     * @param string $repo
     *
     * @return string
     */
    public function getRepoUrl(string $repo): string
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
     * @param mixed   $default
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
     */
    public function setWorkDir(string $workDir): void
    {
        $this->workDir = $workDir;
    }
}
