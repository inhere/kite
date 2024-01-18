<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\DevCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * @author inhere
 */
class ProjectCmd extends Command
{
    protected static string $name = 'project';
    protected static string $desc = 'quick create new project or package or library tool commands';


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
     * @return int
     */
    protected function execute(Input $input, Output $output): int
    {
        $output->println('TODO');
        return 0;
    }
}