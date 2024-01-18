<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitlabCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * Class GenerateYmlCmd
 *
 * @package Inhere\Kite\Console\Controller\Gitlab
 */
class GenerateYmlCmd extends Command
{
    protected static string $name = 'gen-yml';
    protected static string $desc = 'generate apply template contents for k8s';

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
