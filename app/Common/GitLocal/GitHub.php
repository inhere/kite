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
    private $curOwner = '';

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
     * @param string $owner
     * @param string $pName
     */
    public function setCurrent(string $owner, string $pName): void
    {
        $this->curOwner  = $owner;
        $this->curPjName = $pName;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        throw new RuntimeException('The github host is fixed, cannot change it.');
    }
}
