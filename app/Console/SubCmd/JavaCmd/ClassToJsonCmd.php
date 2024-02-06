<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\JavaCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use Inhere\Kite\Console\Component\ContentsAutoWriter;
use Inhere\Kite\Lib\Parser\Java\JavaDTOParser;
use Toolkit\Stdlib\Json;

/**
 * Class ClassToJsonCmd
 */
class ClassToJsonCmd extends Command
{
    protected static string $name = 'to-json';
    protected static string $desc = 'parse java DTO class, convert fields to json or json5';

    public static function aliases(): array
    {
        return ['json'];
    }

    protected function configure(): void
    {
        $this->flags->addOptsByRules([
            'source,s'          => 'string;The class code or filepath to parse. allow use @c for clipboard;required',
            'output,o'          => 'string;The output target for result;;stdout',
            'json5,5'           => 'bool;set output json5 format data, will with comment',
            'start,pos'         => 'int;The start position for parse. 0=header, 1=class, 2=body;;0',
            'inline-comment,ic' => 'bool;use inline comment on output the json5 data',
        ]);
    }

    /**
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

        $m = JavaDTOParser::parse($source, [
            'startPos' => $fs->getOpt('start'),
        ]);

        // output json
        if (!$fs->getOpt('json5')) {
            $map = [];
            foreach ($m->fields as $field) {
                $map[$field->name] = $field->exampleValue();
            }

            $str = Json::pretty($map);
        } else {
            // output json5
            $str = $m->toJSON5([
                'inlineComment' => $fs->getOpt('inline-comment'),
            ]);
        }

        ContentsAutoWriter::writeTo($fs->getOpt('output'), $str);
        return 0;
    }
}
