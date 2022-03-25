<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Attach\Gitlab;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Toolkit\PFlag\FlagsParser;

/**
 * Class BranchInitCmd
 *
 * @package Inhere\Kite\Console\Controller\Gitlab
 */
class BranchInitCmd extends Command
{
    protected static string $name = 'init';
    protected static string $desc = 'quick init testing, qa, pre branches for gitlab project';

    protected function configFlags(FlagsParser $fs): void
    {
        // $this->flags->addOptByRule($name, $rule);
    }

    /**
     * Do execute command
     *
     * @param Input  $input
     * @param Output $output
     *
     * @return int|mixed
     */
    protected function execute(Input $input, Output $output): mixed
    {
        $output->println(__METHOD__);
        return 0;
    }
}
