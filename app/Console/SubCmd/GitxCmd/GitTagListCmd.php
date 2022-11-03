<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitxCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;

/**
 * class GitTagListCmd
 *
 * @author inhere
 * @date 2022/7/12
 */
class GitTagListCmd extends Command
{
    protected static string $name = 'list';
    protected static string $desc = 'list git tags for project';

    public static function aliases(): array
    {
        return ['ls'];
    }

    protected function getArguments(): array
    {
        return [
            'keywords' => 'match special tag by keywords',
        ];
    }

    protected function execute(Input $input, Output $output)
    {
        $fs = $this->flags;

        // git tag --sort=-creatordate 倒序排列
        $cmd = 'git tag -l -n2';
        $kw  = $fs->getArg('keywords');
        if ($kw) {
            $cmd .= " | grep $kw";
        }

        CmdRunner::new($cmd)->do(true);

        $output->success('Complete');
    }
}
