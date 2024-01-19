<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\JavaCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use Inhere\Kite\Lib\Parser\Java\JavaDtoParser;

/**
 * Class MetadataCmd
 */
class MetadataCmd extends Command
{
    protected static string $name = 'metadata';
    protected static string $desc = 'parse java DTO class, collect field metadata information';

    public static function aliases(): array
    {
        return ['md', 'meta'];
    }

    protected function configure(): void
    {
        // $this->flags->addOptByRule($name, $rule);
    }

    /**
     * @options
     *   -s,--source       The source code file or contents.
     *                     if input '@c', will read from clipboard
     *   -o,--output       The output target. default is stdout;;stdout
     *   -f,--format       The output format. default is json;;json
     *
     * @param Input  $input
     * @param Output $output
     *
     * @return int
     */
    protected function execute(Input $input, Output $output): int
    {
        $fs = $this->flags;

        $source = $fs->getOpt('source');
        $source = ContentsAutoReader::readFrom($source);

        $p = JavaDtoParser::parse($source);

        $output->prettyJSON($p->toArray());
        return 0;
    }
}
