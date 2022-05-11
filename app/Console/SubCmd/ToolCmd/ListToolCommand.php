<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\ToolCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Manager\ToolManager;
use Inhere\Kite\Kite;
use Toolkit\Stdlib\Helper\Assert;

/**
 * class InstallCommand
 *
 * @author inhere
 */
class ListToolCommand extends Command
{
    protected static string $name = 'list';
    protected static string $desc = 'list all tools form configure';

    public static function aliases(): array
    {
        return ['l', 'ls'];
    }

    protected function configure(): void
    {
        $this->flags->addArgByRule('name', 'show the tool detail info by name');
        // $this->flags->addOpt('show', 's', 'show the tool info', 'bool');
        // $this->flags->addOpt('list', 'l', 'list all can be installed tools', 'bool');
    }

    protected function execute(Input $input, Output $output)
    {
        $fs = $this->flags;
        /** @var ToolManager $tm */
        $tm = Kite::get('toolManager');
        Assert::isFalse($tm->isEmpty(), 'not config any tools information');

        $name = $fs->getArg('name');
        if ($name) {
            $tool = $tm->getToolModel($name);
            $output->aList($tool->toArray());
            return;
        }

        $output->aList($tm->getToolsInfo(), 'Tools');
    }
}
