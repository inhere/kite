<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitxCmd;

use Inhere\Console\Command;
use Inhere\Console\Component\Symbol\GitEmoji;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;

/**
 * class GitTagListCmd
 *
 * @author inhere
 * @date 2022/7/12
 */
class GitEmojiCmd extends Command
{
    protected static string $name = 'emoji';
    protected static string $desc = 'git emoji list or search by keywords';

    public static function aliases(): array
    {
        return ['moji', 'emj'];
    }

    protected function getArguments(): array
    {
        return [
            'keywords' => 'match special emoji by keywords',
        ];
    }

    protected function execute(Input $input, Output $output)
    {
        $fs = $this->flags;
        $ge = GitEmoji::new();

        if ($kw = $fs->getArg('keywords')) {
            $matched = $ge->search($kw);
            if ($matched) {
                $output->table($matched, "matched emojis by '$kw'");
            } else {
                $output->info('not found matched emojis');
            }
            return;
        }

        $output->table($ge->getEmojis());
    }
}
