<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitxCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Throwable;

/**
 * class AddCommitCmd
 *
 * @author inhere
 * @date 2022/7/12
 */
class AddCommitCmd extends Command
{
    protected static string $name = 'ac';
    protected static string $desc = 'run git add/commit at once command';

    public static function aliases(): array
    {
        return ['add-commit'];
    }

    /**
     * @options
     *  -m, --message    string;The commit message;required
     *
     * @arguments
     *  files   Only add special files
     *
     * @param Input $input
     * @param Output $output
     *
     * @return void
     * @throws Throwable
     */
    protected function execute(Input $input, Output $output): void
    {
        $flags = $this->flags->getFlags();
        $flags[] = '--np';

        $upCmd = new AddCommitPushCmd($input, $output);
        $upCmd->run($flags);
    }
}
