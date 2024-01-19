<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\JavaCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * Class ClassToJsonCmd
 */
class ClassToJsonCmd extends Command
{
    protected static string $name = 'to-json';
    protected static string $desc = 'parse java DTO class, convert fields to json';

    protected function configure(): void
    {
        // $this->flags->addOptByRule($name, $rule);
        $this->flags->addOptsByRules([
            'class' => 'string;The class code or path to parse;required',
            'json5,5' => 'bool;set output json5 format data',
        ]);
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
        $output->println(__METHOD__);
        return 0;
    }
}
