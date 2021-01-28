<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitLocal;

use RuntimeException;

/**
 * Class GitHub
 *
 * @package Inhere\Kite\Common\Git
 */
class GitHub extends AbstractGitLocal
{
    /**
     * current project owner/group name
     *
     * @var string
     */
    private $curGroup = '';

    /**
     * @var string
     */
    private $srcBranch = '';

    /**
     * @var string
     */
    private $dstBranch = '';

    protected function init(array $config): void
    {
        $this->host = self::GITHUB_HOST;

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
