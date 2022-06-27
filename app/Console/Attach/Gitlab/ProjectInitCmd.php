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
class ProjectInitCmd extends Command
{
    protected static string $name = 'init';
    protected static string $desc = 'init a gitlab project information';

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
    protected function execute(Input $input, Output $output): mixed
    {
        $output->println(__METHOD__);
        return 0;
    }
}
