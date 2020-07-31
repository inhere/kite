<?php declare(strict_types=1);

namespace Inhere\Kite\Common\GitLocal;

use Toolkit\Sys\Sys;

/**
 * Class GitUse
 *
 * @package Inhere\Kite\Common\GitLocal
 */
class GitUse extends AbstractGitLocal
{
    /**
     * Error code
     *
     * @var integer
     */
    private $errCode = 0;

    /**
     * Error message
     *
     * @var string
     */
    private $errMsg = '';

    public function findLastTag(): string
    {
        return '';
    }

    /**
     * find changed or new created files by git status.
     * @throws \RuntimeException
     */
    public function findChangedFiles()
    {
        // -u expand dir's files
        [, $output,] = Sys::run('git status -s -u', $this->workDir);

        // 'D some.file'    deleted
        // ' M some.file'   modified
        // '?? some.file'   new file
        foreach (explode("\n", trim($output)) as $file) {
            $file = trim($file);

            // only php file.
            if (!strpos($file, '.php')) {
                continue;
            }

            // modified files
            if (strpos($file, 'M ') === 0) {
                yield substr($file, 2);

                // new files
            } elseif (strpos($file, '?? ') === 0) {
                yield substr($file, 3);
            }
        }
    }

    /**
     * Get the value of errCode
     *
     * @return int
     */
    public function getErrCode(): int
    {
        return $this->errCode;
    }

    /**
     * Get error message
     *
     * @return string
     */
    public function getErrMsg(): string
    {
        return $this->errMsg;
    }
}
