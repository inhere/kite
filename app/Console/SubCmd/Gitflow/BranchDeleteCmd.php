<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\Gitflow;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Toolkit\PFlag\FlagsParser;

/**
 * Class BranchDeleteCmd
 *
 * @package Inhere\Kite\Console\Controller\Gitlab
 */
class BranchDeleteCmd extends Command
{
    protected static string $name = 'delete';
    protected static string $desc = 'quick delete git branches from local, origin, main remote';

    public static function aliases(): array
    {
        return ['del', 'rem', 'rm'];
    }

    protected function configFlags(FlagsParser $fs): void
    {
        // $this->flags->addOptByRule($name, $rule);
    }

    /**
     * delete branches from local, origin, main remote
     *
     * @options
     *  -f, --force         bool;Force execute delete command, ignore error
     *  --nm, --not-main    bool;Dont delete branch on the main remote
     *
     * @arguments
     *  branches...   array;The want deleted branch name(s). eg: fea_6_12;required
     *
     * @param Input $input
     * @param Output $output
     *
     * @return mixed
     */
    protected function execute(Input $input, Output $output): mixed
    {
        $output->println(__METHOD__);
        return 0;
    }
}
