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

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        throw new RuntimeException('The github host is fixed, cannot change it.');
    }
}
