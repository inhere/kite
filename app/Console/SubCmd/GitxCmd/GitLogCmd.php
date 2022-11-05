<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitxCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use PhpGit\Git;
use function abs;
use function date;

/**
 * class GitLogCmd
 *
 * @author inhere
 * @date 2022/7/12
 */
class GitLogCmd extends Command
{
    protected static string $name = 'log';
    protected static string $desc = 'display recently git commits information by `git log`';

    public static function aliases(): array
    {
        return ['lg', 'l'];
    }

    protected function getArguments(): array
    {
        return [
            // 'keywords' => 'match special tag by keywords',
        ];
    }


    /**
     * display recently git commits information by `git log`
     *
     * @arguments
     *  maxCommit       int;Max display how many commits;;15
     *
     * @options
     *  --ac, --abbrev-commit     bool;Only display the abbrev commit ID
     *  --exclude                 Exclude contains given sub-string. multi by comma split.
     *  --file                    Export changelog message to file
     *  --format                  The git log option `--pretty` value.
     *                            can be one of oneline, short, medium, full, fuller, reference, email, raw, format:<string> and tformat:<string>.
     *  --mc, --max-commit        int;Max display how many commits
     *  --nc, --no-color          bool;Dont use color render git output
     *  --nm, --no-merges         bool;No contains merge request logs
     *
     * @param Input $input
     * @param Output $output
     *
     * @return void
     */
    protected function execute(Input $input, Output $output)
    {
        $fs = $this->flags;

        $noColor = $fs->getOpt('no-color');
        $exclude = $fs->getOpt('exclude');

        $noMerges  = $fs->getOpt('no-merges');
        $abbrevID  = $fs->getOpt('abbrev-commit');
        $maxCommit = $fs->getOpt('max-commit', $fs->getArg('maxCommit'));

        $b = Git::new()->log->builder();

        // git log --color --graph --pretty=format:'%Cred%h%Creset:%C(ul yellow)%d%Creset %s (%Cgreen%cr%Creset, %C(bold blue)%an%Creset)' --abbrev-commit -10
        $b->add('--graph');
        $b->addIf('--color', !$noColor);
        $b->add('--pretty=format:"%Cred%h%Creset:%C(ul yellow)%d%Creset %s (%Cgreen%cr%Creset, %C(bold blue)%an%Creset)"');
        $b->addIf("--exclude=$exclude", $exclude);
        $b->addIf('--abbrev-commit', $abbrevID);
        $b->addIf('--no-merges', $noMerges);
        $b->add('-' . abs($maxCommit));

        $b->runAndPrint();

        $output->success('Complete at ' . date('Y-m-d H:i:s'));
    }
}
