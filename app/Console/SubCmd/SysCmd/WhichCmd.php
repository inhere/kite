<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\SysCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * Class JsonToDTOCmd
 */
class WhichCmd extends Command
{
    protected static string $name = 'gen-dto';
    protected static string $desc = 'convert create SQL/JSON/JSON5 contents to JAVA entity,dto class';

    protected function configure(): void
    {
        // $this->flags->addOptByRule($name, $rule);
    }

    /**
     * @options
     *   -s,--source       The source code file or contents. if input '@', will read from clipboard
     *   -o,--output       The output target. default is stdout;;stdout
     *
     * @param Input  $input
     * @param Output $output
     *
     * @return int
     */
    protected function execute(Input $input, Output $output): int
    {
        $output->success('TODO');
        return 0;
    }
}
