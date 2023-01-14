<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitxCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use PhpGit\Git;
use Toolkit\FsUtil\Dir;
use Toolkit\PFlag\FlagsParser;

/**
 * Class BranchDeleteCmd
 *
 * @package Inhere\Kite\Console\Controller\Gitlab
 */
class BatchStatusCmd extends Command
{
    protected static string $name = 'status';
    protected static string $desc = 'quick check git status for multi repository dir';

    public static function aliases(): array
    {
        return ['st'];
    }

    protected function configFlags(FlagsParser $fs): void
    {
        // $this->flags->addOptByRule($name, $rule);
    }

    /**
     * @options
     *  -i, --include         Include match filter
     *  -e, --exclude         Exclude match filter
     *
     * @arguments
     *  dirs...   array;The parent dir for multi git repository;required
     *
     * @param Input $input
     * @param Output $output
     *
     * @return int
     */
    protected function execute(Input $input, Output $output): int
    {
        $fs   = $this->flags;
        $dirs = $fs->getArg('dirs');

        foreach ($dirs as $dir) {
            $output->colored("In the parent dir: " . $dir);

            foreach (Dir::getDirs($dir) as $subDir) {
                $gitDir = Dir::join($dir, $subDir);
                $output->colored("- In the git repo dir: " . $gitDir);

                Git::new($gitDir)->status->display();
            }
        }

        return 0;
    }
}
