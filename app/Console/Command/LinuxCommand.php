<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Command;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Toolkit\PFlag\FlagsParser;

/**
 * class LinuxCommand
 *
 * @author inhere
 */
class LinuxCommand extends Command
{
    protected static string $name = 'linux';

    protected static string $desc = 'Useful documents for linux tool commands';

    /**
     * // 结构使用 https://raw.githubusercontent.com/jaywcjlove/linux-command/master/dist/data.json
     * type commandIndex struct {
     *  Name        string `json:"n"`
     *  Path        string `json:"p"`
     *  Description string `json:"d"`
     * }
     */
    public const REPO_URL = 'https://github.com/jaywcjlove/linux-command';

    /**
     * @param FlagsParser $fs
     *
     * @return void
     */
    protected function configFlags(FlagsParser $fs): void
    {
        $fs->addOptByRule('update, up', 'bool;update linux command docs to latest');
        $fs->addOptByRule('init, i', 'bool;update linux command docs to latest');
        $fs->addOptByRule('search, s', 'string;input keywords for search');

        $fs->addArg('keywords', 'the keywords for search or show docs', 'string');
    }

    /**
     * Do execute command
     *
     * @param Input $input
     * @param Output $output
     *
     * @return mixed|void
     */
    protected function execute(Input $input, Output $output)
    {
        // TODO: Implement execute() method.
        $output->info('TODO');
    }
}
