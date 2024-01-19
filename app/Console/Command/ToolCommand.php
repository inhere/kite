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
use Inhere\Console\Handler\CallableCommand;
use Inhere\Console\Handler\CommandWrapper;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\SubCmd\GitxCmd\GitEmojiCmd;
use Inhere\Kite\Console\SubCmd\OpenCmd;
use Inhere\Kite\Console\SubCmd\ToolCmd\BatchCommand;
use Inhere\Kite\Console\SubCmd\ToolCmd\CatCommand;
use Inhere\Kite\Console\SubCmd\ToolCmd\ExprCommand;
use Inhere\Kite\Console\SubCmd\ToolCmd\FindCommand;
use Inhere\Kite\Console\SubCmd\ToolCmd\HashCommand;
use Inhere\Kite\Console\SubCmd\ToolCmd\HashHmacCommand;
use Inhere\Kite\Console\SubCmd\ToolCmd\Json5Command;
use Inhere\Kite\Console\SubCmd\ToolCmd\LnCommand;
use Inhere\Kite\Console\SubCmd\ToolCmd\MarkdownCommand;
use Inhere\Kite\Console\SubCmd\ToolCmd\SearchCommand;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Sys\Sys;

/**
 * Class ToolCommand
 */
class ToolCommand extends Command
{
    public const OPT_DRY_RUN   = 'dry-run';
    public const OPT_PROXY_ENV = 'proxy-env';

    protected static string $name = 'tool';
    protected static string $desc = 'provide some little tool commands';

    protected function subCommands(): array
    {
        $this->addSub('which', CallableCommand::wrap(
            fn(FlagsParser $fs, Output $output) => $this->runWhichCmd($fs, $output),
            $this->whichCmdConfig())
        );

        return [
            OpenCmd::class,
            LnCommand::class,
            HashHmacCommand::class,
            HashCommand::class,
            Json5Command::class,
            ExprCommand::class,
            MarkdownCommand::class,
            BatchCommand::class,
            CatCommand::class,
            FindCommand::class,
            SearchCommand::class,
            GitEmojiCmd::class,
        ];
    }

    protected function configure(): void
    {
        $this->flags->addOptByRule(self::OPT_DRY_RUN . ',try', 'bool;Dry-run the workflow, dont real execute');
        $this->flags->addOptByRule(self::OPT_PROXY_ENV, 'bool;open proxy env settings on run command');
    }

    /**
     * @param Input  $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output): void
    {
        $this->showHelp();
    }

    protected function whichCmdConfig(): array
    {
        return [
            'desc'      => 'find bin file path, like system `which`',
            'aliases'   => ['where', 'whereis'],
            'options'   => [
                // '--clean' => 'bool;clean output, only output path.'
            ],
            'arguments' => [
                'binName' => 'string;the target bin file name for find;true',
            ],
        ];
    }

    protected function runWhichCmd(FlagsParser $fs, Output $output): void
    {
        $name = $fs->getArg('binName');
        $path = Sys::findExecutable($name);
        if (!$path) {
            $output->println('Not found in PATH');
            return;
        }

        // $clean = $fs->getOpt('clean');
        $output->colored($path);
    }
}
