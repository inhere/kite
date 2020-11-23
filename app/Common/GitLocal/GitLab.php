<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitLocal;

use Inhere\Kite\Common\GitLocal\GitLab\Project;

/**
 * Class GitLab
 *
 * @package Inhere\Kite\Common\Git
 */
class GitLab extends AbstractGitLocal
{
    /**
     * @var string
     */
    private $srcBranch = '';

    /**
     * @var string
     */
    private $dstBranch = '';

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
        }

        if (!$pjName) {
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
     * @return Project
     */
    public function getCurProject(): Project
    {
        return Project::new($this->curPjInfo);
    }
}
