<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\ToolCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use function hash;
use function md5;
use function strtolower;
use function strtoupper;

/**
 * class HashHmacCommand
 *
 * @author inhere
 */
class HashCommand extends Command
{
    protected static string $name = 'hash';
    protected static string $desc = 'Generate a keyed hash value using the given algo method';

    protected function configure(): void
    {
        // $this->flags->addOptByRule($name, $rule);
        $this->flags->addArg('str', 'want to signed string. allow: @c', 'string', true);
        $this->flags->addOpt('algo', 'a', 'Name of selected hashing algorithm. eg: md5, sha256', 'string', false, 'md5');
        $this->flags->addOpt('upper', 'u', 'Convert hashed value to upper case', 'bool');
        // $this->flags->addOpt(
        //     'key', 'k',
        //     'Shared secret key used for generating the HMAC variant of the message digest.',
        //     'string', true);
    }

    protected function execute(Input $input, Output $output)
    {
        $fs = $this->flags;

        // $secKey = $fs->getOpt('key');
        $rawStr = $fs->getArg('str');
        $rawStr = ContentsAutoReader::readFrom($rawStr);

        $algoName = strtolower($fs->getOpt('algo'));
        $output->aList([
            // 'key'  => $secKey,
            'algo' => $algoName,
        ]);
        $output->colored('RAW STRING:');
        $output->println($rawStr);

        if ($algoName === 'md5') {
            $signStr = md5($rawStr);
        } else {
            $signStr = hash($algoName, $rawStr);
        }

        if ($fs->getOpt('upper')) {
            $signStr = strtoupper($signStr);
        }

        $output->colored('SIGN:');
        $output->println($signStr);
    }
}
