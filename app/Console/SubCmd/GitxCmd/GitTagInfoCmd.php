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
class GitTagInfoCmd extends Command
{
    protected static string $name = 'info';
    protected static string $desc = 'display git tag information by `git show TAG`';

    public static function aliases(): array
    {
        return ['show'];
    }
    protected function getArguments(): array
    {
        return [
            'tag' => 'string;Tag name for show info;required',
        ];
    }

    /**
     * display git tag information by `git show TAG`
     *
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $fs = $this->flags;
        $tag = $fs->getArg('tag');

        $commands = [
            "git show $tag",
        ];

        CmdRunner::new()->batch($commands)->runAndPrint();
        $output->success('Complete');
    }
}
