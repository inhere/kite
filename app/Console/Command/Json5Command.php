<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Command;

use ColinODell\Json5\Json5Decoder;
use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use Inhere\Kite\Console\Component\ContentsAutoWriter;
use JsonException;
use function file_put_contents;
use function json_encode;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * Class FindCommand
 */
class Json5Command extends Command
{
    protected static $name = 'json5';

    protected static $desc = 'read and convert json5 file to json format';

    public static function aliases(): array
    {
        return ['j5'];
    }

    /**
     * @options
     *  -o, --output     string;Output the decoded contents to the file;;stdout
     *
     * @arguments
     *  json5        string;The json5 source for parse, allow: FILEPATH,@clipboard;required
     *
     * @param Input $input
     * @param Output $output
     *
     * @throws JsonException
     */
    protected function execute(Input $input, Output $output)
    {
        $source  = ContentsAutoReader::readFrom($this->flags->getArg('json5'));
        $decoded = Json5Decoder::decode($source);

        $encFlag = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        $jsonStr = json_encode($decoded, JSON_THROW_ON_ERROR | $encFlag);

        $outFile = $this->flags->getOpt('output');

        ContentsAutoWriter::writeTo($outFile, $jsonStr);
    }
}
