<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\JavaCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * Class GenerateDTOCmd
 */
class GenerateDTOCmd extends Command
{
    protected static string $name = 'gen-dto';
    protected static string $desc = 'generate DTO class from create SQL/JSON/JSON5 contents';

    public static function aliases(): array
    {
        return ['to-dto', 'dto'];
    }

    protected function configure(): void
    {
        // $this->flags->addOptByRule($name, $rule);
    }

    /**
     * @options
     *   -s,--source       The source code file or contents. if input '@c', will read from clipboard
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
