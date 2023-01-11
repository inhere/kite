<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\ToolCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * class LnCommand
 *
 * @author inhere
 */
class SnippetCommand extends Command
{
    protected static string $name = 'snippet';

    protected static string $desc = 'provide useful code snippet manage';

    /**
     * @return string[]
     */
    public static function aliases(): array
    {
        return ['snippets', 'snip'];
    }

    protected function configure(): void
    {
        $this->flags->addArgByRule('name', 'show the tool detail info by name');
        // $this->flags->addOpt('show', 's', 'show the tool info', 'bool');
        // $this->flags->addOpt('list', 'l', 'list all can be installed tools', 'bool');
    }

    protected function execute(Input $input, Output $output)
    {

        $output->info('TODO');
    }
}
