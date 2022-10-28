<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitLocal;

use RuntimeException;

/**
 * Class GitHub
 *
 * @package Inhere\Kite\Common\Git
 */
class GitHub extends AbstractGitx
{
    public const HOST = GitConst::GITHUB_HOST;
    public const GIT_HOST = GitConst::GITHUB_HOST;

    /**
     * current project owner/group name
     *
     * @var string
     */
    private string $curGroup = '';

    /**
     * @var string
     */
    private string $srcBranch = '';

    /**
     * @var string
     */
    private string $dstBranch = '';

    protected function init(array $config): void
    {
        $this->host = GitConst::GITHUB_HOST;

        parent::init($config);
    }

    /**
     * @param string $group
     * @param string $pName
     */
    public function setCurrent(string $group, string $pName): void
    {
        $this->curGroup  = $group;
        $this->curPjName = $pName;
    }

    /**
     * @return GitProject
     */
    public function getCurProject(): GitProject
    {
        return GitProject::new($this->curPjInfo);
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        throw new RuntimeException('The github host is fixed, cannot change it.');
    }
}
