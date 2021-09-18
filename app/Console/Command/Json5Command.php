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
use InvalidArgumentException;
use function file_get_contents;
use function file_put_contents;
use function is_file;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * Class FindCommand
 */
class Json5Command extends Command
{
    protected static $name = 'json5';

    protected static $description = 'read and convert json5 file to json format';

    public static function aliases(): array
    {
        return ['j5'];
    }

    protected function configure(): void
    {
        $this->input->bindArgument('json5file', 0);
    }

    /**
     * @options
     *  -o, --output     Output the decoded contents to the file
     *
     * @arguments
     *  json5file        The script name for execute
     *
     * @param Input  $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $j5file = $this->flags->getArg('json5file');
        if (!is_file($j5file)) {
            throw new InvalidArgumentException("the json5 file '$j5file' is not exists");
        }

        $source  = file_get_contents($j5file);
        $decoded = Json5Decoder::decode($source);

        $encFlag = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        $outFile = $this->flags->getOpt('output');
        if ($outFile) {
            file_put_contents($outFile, json_encode($decoded, $encFlag));
            $output->success('write contents to output file');
        }

        echo json_encode($decoded, $encFlag), "\n";
    }
}
