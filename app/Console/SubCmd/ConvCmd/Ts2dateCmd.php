<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\ConvCmd;

use Inhere\Console\Command;
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Component\Clipboard;
use Inhere\Kite\Helper\KiteUtil;
use Toolkit\PFlag\FlagsParser;
use function count;
use function date;
use function preg_match_all;
use function strlen;

/**
 * Class Ts2dateCmd
 *
 * @package Inhere\Kite\Console\Controller\Gitlab
 */
class Ts2dateCmd extends Command
{
    protected static string $name = 'ts2date';
    protected static string $desc = 'quick convert all timestamp number to datetime';

    public static function aliases(): array
    {
        return ['t2d', 'ts', 'td'];
    }

    protected function configFlags(FlagsParser $fs): void
    {
        // $this->flags->addOptByRule($name, $rule);
    }

    /**
     * @arguments
     *  times    array;The want convert timestamps, allow @clipboard;true
     *
     * @param Input $input
     * @param Output $output
     *
     * @return mixed
     */
    protected function execute(Input $input, Output $output): mixed
    {
        $fs = $this->flags;

        $args = $fs->getArg('times');
        if (count($args) === 1 && KiteUtil::isClipboardAlias($args[0])) {
            $text = Clipboard::new()->read();
            $args = $text ? [$text] : [];

            if (!$args) {
                throw new PromptException('no contents in clipboard');
            }
        }

        $output->info('Input Data:');
        $output->writeRaw($args);

        $data = [];
        foreach ($args as $time) {
            if (strlen($time) > 10) {
                preg_match_all('/1\d{9}/', $time, $matches);
                if (empty($matches[0])) {
                    $output->warning("not found time in the: $time");
                    continue;
                }

                foreach ($matches[0] as $match) {
                    $data[] = [
                        'timestamp' => $match,
                        'datetime'  => date('Y-m-d H:i:s', (int)$match),
                    ];
                }
                continue;
            }

            $data[] = [
                'timestamp' => $time,
                'datetime'  => date('Y-m-d H:i:s', (int)$time),
            ];
        }

        $output->info('Parsed Result:');
        // opts
        $output->table($data, 'Time to date', []);
        $output->colored('> Current Time: ' . date('Y-m-d H:i:s'));
        return 0;
    }
}
