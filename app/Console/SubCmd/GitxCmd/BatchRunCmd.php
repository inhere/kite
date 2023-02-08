<?php declare(strict_types=1);

namespace Inhere\Kite\Console\SubCmd\GitxCmd;

use Inhere\Console\Command;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\Cmd;
use Toolkit\FsUtil\Dir;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Str;

/**
 * Class BatchRunCmd
 */
class BatchRunCmd extends Command
{
    protected static string $name = 'run';
    protected static string $desc = 'batch run custom command on multi repository dir';

    public static function aliases(): array
    {
        return ['exec'];
    }

    protected function configFlags(FlagsParser $fs): void
    {
        // $this->flags->addOptByRule($name, $rule);
    }

    /**
     * @options
     *  -i, --include         Include match filter
     *  -e, --exclude         Exclude match filter
     *  -c, --cmd             Exec the command on each git repo dir
     *                        Built in vars:
     *                          {dir}           current dir path.
     *                          {d_name}        current dir name.
     *                          {p_dir}         parent dir path.;true
     * @arguments
     *  dirs...         array;The parent dir for multi git repository;required
     *
     * @param Input $input
     * @param Output $output
     *
     * @return int
     * @example
     *   {cmdPath} -c 'pwd' ../
     *   {cmdPath} -c 'git status' ../
     *   {cmdPath} -c 'echo {d_name}' ../
     *   {cmdPath} -c 'kite git log 3' ../
     */
    protected function execute(Input $input, Output $output): int
    {
        $fs   = $this->flags;
        $cmd  = $fs->getOpt('cmd');
        $dirs = $fs->getArg('dirs');

        foreach ($dirs as $dir) {
            $output->colored("In the parent dir: " . $dir);
            $vars = [
                'p_dir' => $dir,
            ];

            foreach (Dir::getDirs($dir) as $subDir) {
                $repoDir = Dir::join($dir, $subDir);

                $output->colored("- In the git repo dir: " . $repoDir);
                $vars['dir']    = $repoDir;
                $vars['d_name'] = $subDir;

                $cmdline = Str::renderVars($cmd, $vars, '{%s}');
                Cmd::new('', $repoDir)->setCmdline($cmdline)->runAndPrint();
            }
        }

        return 0;
    }
}
