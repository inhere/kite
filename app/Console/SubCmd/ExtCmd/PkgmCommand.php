<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\SubCmd\ExtCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Console\SubCmd\PkgmCmd\InstallCommand;
use Inhere\Kite\Console\SubCmd\PkgmCmd\ListCommand;
use Inhere\Kite\Console\SubCmd\PkgmCmd\UpdateCommand;

/**
 * Class ToolCommand
 */
class PkgmCommand extends Command
{
    public const OPT_DRY_RUN   = 'dry-run';
    public const OPT_PROXY_ENV = 'proxy-env';

    protected static string $name = 'pkgm';
    protected static string $desc = 'lightweight package manager tool';

    public static function aliases(): array
    {
        return ['pkgx', 'appm'];
    }

    protected function subCommands(): array
    {
        return [
            InstallCommand::class,
            UpdateCommand::class,
            ListCommand::class,
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
