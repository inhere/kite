<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use function parse_str;
use function rawurldecode;

/**
 * Class ParseUrlQueryCmd
 */
class ParseUrlQueryCmd extends Command
{
    protected static string $name = 'dequery';
    protected static string $desc = 'decode URL query string and parse it';

    public static function aliases(): array
    {
        return ['dq'];
    }

    protected function configure(): void
    {
        // $this->flags->addOptByRule($name, $rule);
        $this->flags->addArg('query', 'The URI query string. allow: @clipboard', 'string', true);
    }

    /**
     * @param Input $input
     * @param Output $output
     *
     * @return int|mixed
     */
    protected function execute(Input $input, Output $output): mixed
    {
        $fs = $this->flags;

        $query = $fs->getArg('query');

        $str = ContentsAutoReader::readFrom($query);
        $str = rawurldecode($str);
        // println($str);

        // if (!$fs->getOpt('full')) {
        //     $str = 'http://abc.com?' . $str;
        // }
        parse_str($str, $ret);

        $output->aList($ret, 'Query Data:');
        return 0;
    }
}
