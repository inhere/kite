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

/**
 * Class ToolCommand
 */
class ToolCommand extends Command
{
    public const OPT_DRY_RUN   = 'dry-run';
    public const OPT_PROXY_ENV = 'proxy-env';

    protected static string $name = 'tool';
    protected static string $desc = 'some little tool commands';

    protected function subCommands(): array
    {
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
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $this->showHelp();
    }
}
