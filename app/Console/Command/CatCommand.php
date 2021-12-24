<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Command;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use PhpPkg\Config\ConfigBox;
use PhpPkg\Config\ConfigUtil;

/**
 * Class CatCommand
 */
class CatCommand extends Command
{
    protected static string $name = 'cat';

    protected static string $desc = 'read and show contents';

    protected function configure(): void
    {
        $this->flags
            ->addOpt('type', 't', 'content type. allow: raw, txt, json', 'string', false, 'raw')
            ->addArg('source', <<<'TXT'
The source contents.
Special input:
 input '@c' or '@cb' or '@clipboard' - will read from Clipboard
 input empty or '@i' or '@stdin'     - will read from STDIN
 input '@FILEPATH'                   - will read from the filepath
TXT
);
    }

    /**
     * @param  Input $input
     * @param  Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $txt = ContentsAutoReader::readFrom($this->flags->getArg('source'), [
            'throwOnEmpty' => false
        ]);
        if (!$txt) {
            return;
        }

        switch ($this->flags->getArg('type')) {
            case 'yml':
            case 'yaml':
                ConfigUtil::readFromString(ConfigBox::FORMAT_YAML, $txt);
                break;
            case 'raw':
            case 'txt':
            case 'text':
            default:
                $output->writeln($txt);
        }
    }
}
