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
use Inhere\Kite\Common\Cmd;
use Toolkit\Stdlib\Helper\Assert;
use function strtr;

/**
 * Class BatchCommand
 */
class BatchCommand extends Command
{
    /** @var string */
    protected static string $name = 'batch';

    /**
     * @var string
     */
    protected static string $desc = 'batch run an regular command template multi times';

    /**
     * @return string[]
     */
    public static function aliases(): array
    {
        return ['brun', 'batch-run'];
    }

    protected function configure(): void
    {
        $this->flags
            ->addOpt('vars', 'v', 'the vars list use foreach to cmd template,multi split by ","', 'string', true)
            ->addOpt('interval', '', 'the sleep interval time(seconds) for run each cmd', 'int', false, null, [
                'aliases' => ['iv'],
            ])
            ->addOpt('dry-run', '', 'Not real run the command', 'bool', false, null, [
                'aliases' => ['try']
            ])
            ->addArg('cmdTpl', 'command template that will be run', 'string', true);

        $this->flags->setExample(<<<'TXT'
{binWithCmd} --vars dir1,dir2,dir3 "cd {var}; git status"
TXT
);
    }

    /**
     * @param Input $input
     * @param Output $output
     */
    protected function execute(Input $input, Output $output)
    {
        $fs = $this->flags;

        $cmdTpl   = $fs->getArg('cmdTpl');
        $interval = $fs->getOpt('interval');
        Assert::intShouldGte0($interval, 'interval', true);

        $cmd = Cmd::new()->setDryRun($fs->getOpt('dry-run'));
        foreach ($fs->getOptStrAsArray('vars') as $var) {
            $cmdStr = strtr($cmdTpl, [
                '{var}' => $var,
            ]);

            $cmd->setCmdline($cmdStr)->runAndPrint();
            // $cmd->isFail()
        }

        $output->writeRaw($cmdTpl);
    }
}
