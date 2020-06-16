<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitLocal;

use Inhere\Kite\Helper\GitUtil;
use RuntimeException;

/**
 * Class GitHub
 *
 * @package Inhere\Kite\Common\Git
 */
class GitHub extends AbstractGitLocal
{
    /**
     * @var array
     */
    private $projects;

    /**
     * current project owner/group name
     *
     * @var string
     */
    private $curOwner = '';

    /**
     * current project name
     *
     * @var string
     */
    private $curPName = '';

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
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->host = self::GITHUB_HOST;
    }

    /**
     * @param string $owner
     * @param string $pName
     */
    public function setCurrent(string $owner, string $pName): void
    {
        $this->curOwner = $owner;
        $this->curPName = $pName;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        throw new RuntimeException('The github host is fixed, cannot change it.');
    }
}
