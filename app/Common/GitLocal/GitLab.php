<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitLocal;

use Inhere\Kite\Common\GitLocal\GitLab\GlProject;

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
    private string $srcBranch = '';

    /**
     * @var string
     */
    private string $dstBranch = '';


    public function createPRLink(string $srcBranch, string $dstBranch, bool $direct = false): string
    {
        return '';
    }

    /**
     * @return GlProject
     */
    public function getCurProject(): GlProject
    {
        return GlProject::new($this->curPjInfo);
    }
}
