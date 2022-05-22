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
class InstallCommand extends Command
{
    protected static string $name = 'install';
    protected static string $desc = 'install bin tool form configure scripts';

    public static function aliases(): array
    {
        return ['ins', 'in', 'i'];
    }

    protected function configure(): void
    {
        $this->flags->addArgByRule('name', 'want installed tool name');
        $this->flags->addOpt('show', 's', 'show the tool info', 'bool');
        $this->flags->addOpt('list', 'l', 'list all can be installed tools', 'bool');
        // $this->flags->addOpt(
        //     'key', 'k',
        //     'Shared secret key used for generating the HMAC variant of the message digest.',
        //     'string', true);
    }

    protected function execute(Input $input, Output $output)
    {
        $fs = $this->flags;
        /** @var ToolManager $tm */
        $tm = Kite::get('toolManager');
        Assert::isFalse($tm->isEmpty(), 'not config any tools information');

        if ($fs->getOpt('list')) {
            $output->aList($tm->getToolsInfo(), 'Tools');
            return;
        }

        $name = $fs->getArg('name');
        Assert::notEmpty($name, 'want install tool name is required');

        $tool = $tm->getToolModel($name);
        if ($fs->getOpt('show')) {
            $output->aList($tool->toArray());
            return;
        }

        $command = 'install';
        $output->info("Will $command the tool: $name");

        $cr = $tool->buildCmdRunner($command);

        if ($p = $this->getParent()) {
            $cr->setDryRun($p->getFlags()->getOpt('dry-run'));
        }

        // $cr->setEnvVars();
        $tool->run($command, $cr);
    }
}
