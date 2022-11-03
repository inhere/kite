<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitxCmd;

use Inhere\Console\Command;
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;

/**
 * class GitTagDelCmd
 *
 * @author inhere
 * @date 2022/7/12
 */
class GitTagDelCmd extends Command
{
    protected static string $name = 'delete';
    protected static string $desc = 'delete an local and remote tag by `git tag`';

    public static function aliases(): array
    {
        return ['del', 'rm'];
    }

    /**
     * @options
     *  -r, --remote        The remote name. default <comment>origin</comment>
     *  -v, --tag           The tag version. eg: v2.0.3
     *      --no-remote     bool;Only delete local tag
     *
     * @param Input $input
     * @param Output $output
     *
     * @return void
     */
    protected function execute(Input $input, Output $output): void
    {
        $fs = $this->flags;

        $tag = $fs->getOpt('tag');
        if (!$tag) {
            throw new PromptException('please input tag name');
        }

        $run = CmdRunner::new();
        $run->addf('git tag -d %s', $tag);

        if (false === $fs->getOpt('no-remote')) {
            $remote = $fs->getOpt('remote', 'origin');
            $run->addf('git push %s :refs/tags/%s', $remote, $tag);
        }

        $run->runAndPrint();

        $output->success('Complete');
    }
}
