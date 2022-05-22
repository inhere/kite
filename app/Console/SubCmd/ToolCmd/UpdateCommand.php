<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\ToolCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\Manager\ToolManager;
use Inhere\Kite\Kite;
use Toolkit\Stdlib\Helper\Assert;

/**
 * class UpdateCommand
 *
 * @author inhere
 */
class UpdateCommand extends Command
{
    protected static string $name = 'update';
    protected static string $desc = 'update bin tool form configure scripts';

    public static function aliases(): array
    {
        return ['up', 'u'];
    }

    protected function configure(): void
    {
        $this->flags->addArgByRule('name', 'string;want updated tool name;true');
        // $this->flags->addOpt('list', 'l', 'list all can be installed tools', 'bool');
    }

    /**
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $fs = $this->flags;

        /** @var ToolManager $tm */
        $tm = Kite::get('toolManager');
        Assert::isFalse($tm->isEmpty(), 'not config any tools information');

        $name = $fs->getArg('name');
        $tool = $tm->getToolModel($name);

        $command = 'update';
        $output->info("Will $command the tool: $name");

        $cr = $tool->buildCmdRunner($command);

        if ($p = $this->getParent()) {
            $cr->setDryRun($p->getFlags()->getOpt('dry-run'));
        }

        // $cr->setEnvVars();
        $tool->run($command, $cr);
    }
}
