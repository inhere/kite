<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\JavaCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;

/**
 * @author inhere
 */
class JavaCmd extends Command
{
    protected static string $name = 'java';

    protected static string $desc = 'Some useful tool commands for java development';

    protected function subCommands(): array
    {
        return [
            ClassToJsonCmd::class,
            GenerateDTOCmd::class,
            MetadataCmd::class,
            InitProjectCmd::class,
        ];
    }

    protected function configure(): void
    {
        // $this->flags->addOptByRule($name, $rule);
    }

    /**
     * @inheritDoc
     */
    protected function execute(Input $input, Output $output)
    {
        return $this->showHelp();
    }
}