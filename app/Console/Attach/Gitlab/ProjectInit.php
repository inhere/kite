<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Attach\Gitlab;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * Class ProjectInit
 *
 * @package Inhere\Kite\Console\Controller\Gitlab
 */
class ProjectInit extends Command
{
    protected static $name = 'pinit';

    protected function configure(): void
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
    protected function execute(Input $input, Output $output)
    {
        $output->println(__METHOD__);
        return 0;
    }
}
