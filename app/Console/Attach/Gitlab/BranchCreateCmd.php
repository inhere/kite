<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Attach\Gitlab;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Toolkit\PFlag\FlagsParser;

/**
 * Class BranchCreateCmd
 *
 * @package Inhere\Kite\Console\Controller\Gitlab
 */
class BranchCreateCmd extends Command
{
    protected static string $name = 'create';
    protected static string $desc = 'create a new branch for gitlab project';

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
