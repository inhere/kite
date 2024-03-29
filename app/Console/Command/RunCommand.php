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
use Inhere\Console\Util\Show;
use Inhere\Kite\Component\ScriptRunner;
use Inhere\Kite\Kite;
use Toolkit\Stdlib\OS;
use function count;
use function is_array;

/**
 * Class RunCommand
 */
class RunCommand extends Command
{
    protected static string $name = 'run';

    protected static string $desc = 'run an script command or script file or kite plugin';

    /**
     * @var ScriptRunner
     */
    private ScriptRunner $sr;

    /**
     * @return string[]
     */
    public static function aliases(): array
    {
        return ['exec', 'script'];
    }

    protected function beforeExecute(): bool
    {
        $this->sr = Kite::scriptRunner();

        return parent::beforeExecute();
    }

    /**
     * @options
     *  -l, --list          List information for all scripts or script files. type: file, cmd
     *  -i, --info          Show information for give script name or script file
     *      --dry-run       bool;Mock running, not real execute.
     *  -p, --proxy         bool;Enable proxy ENV setting
     *  -w, --workdir       change the workdir
     *
     * @arguments
     *  name        The script/plugin name for execute.
     *
     * @param Input $input
     * @param Output $output
     *
     * @return int
     * @help
     * <cyan>TIPS</cyan>:
     *
     * - In php script, you can load boot file, to use all kite class in the file.
     *
     * ```php
     * // load kite boot file, allow use all class in the kite.
     * if ($kiteBootFile = getenv('KITE_BOOT_FILE')) {
     *      require $kiteBootFile;
     * }
     * ```
     *
     * @example
     *   {binWithCmd} -l cmd
     *   {binWithCmd} -l cmd KEYWORDS
     *   {binWithCmd} -l file
     *   {binWithCmd} -l file KEYWORDS
     *   # run script
     *   {binWithCmd} script-name
     *   # run script file with args
     *   {binWithCmd} hello.sh one two three 'a b c'
     *   # with proxy
     *   {binWithCmd} --proxy hello.sh one two three
     */
    protected function execute(Input $input, Output $output): int
    {
        $workdir = $this->flags->getOpt('workdir');
        if ($workdir) {
            $input->chWorkDir($workdir);
        }
        $output->info('Workdir: ' . $input->getWorkDir());

        $name = $this->flags->getArg('name');
        if ($this->flags->hasInputOpt('list')) {
            $listType = $this->flags->getOpt('list', 'cmd');

            if ($listType === ScriptRunner::TYPE_FILE) {
                $this->listScriptFiles($output, $name);
            } else {
                $this->listScripts($output, $name);
            }

            return 0;
        }

        // support search
        if ($this->flags->hasInputOpt('search')) {
            $kw = $this->flags->getOpt('search') ?: $name;
            $this->searchScripts($output, $kw);
            return 0;
        }

        // default list script commands
        if ($this->flags->hasInputOpt('info')) {
            $name = $this->flags->getOpt('info', $name);
            $this->listScripts($output, $name);
            return 0;
        }

        if (!$name) {
            $output->liteError('please input an name for run or use -l TYPE see all scripts');
            return 0;
        }

        $runner = $this->sr;
        $dryRun = $this->flags->getOpt('dry-run');
        $runner->setDryRun($dryRun);

        // proxy
        $openProxy = $this->flags->getOpt('proxy');
        $proxyEnv  = Kite::config()->getArray('proxyEnv');
        if ($openProxy && $proxyEnv) {
            Show::aList($proxyEnv, 'Set Proxy ENV From Config: "proxyEnv"', [
                'ucFirst'      => false,
                'ucTitleWords' => false,
            ]);

            OS::setEnvVars($proxyEnv);
        }

        // $runArgs = $this->flags->getRawArgs(); // 会包含 arg: name
        $runArgs = $this->flags->getRemainArgs();

        if (!$runner->isScriptName($name)) {
            // - found script file
            if ($scriptFile = $runner->findScriptFile($name)) {
                $runner->runScriptFile($scriptFile, $runArgs);
                return $runner->getErrCode();
            }

            // - is a plugin
            if (Kite::plugManager()->isPlugin($name)) {
                $output->notice("input is an plugin name, will run plugin: $name");
                Kite::plugManager()->run($name, $this->app, $runArgs);
                return 0;
            }

            // as script expr and run.
            $runner->runInputScript($name);
            // $output->liteError("please input an exists script name for run. ('$name' not exists)");
        } else {
            // run script by name
            $runner->runScriptByName($name, $runArgs);
        }

       return $runner->getErrCode();
    }

    /**
     * @param Output $output
     * @param string $name
     */
    private function listScriptFiles(Output $output, string $name): void
    {
        $files = $this->sr->getAllScriptFiles($name);
        $count = count($files);

        $appendTitle = $name ? ", keyword:$name" : '';

        $output->aList($this->sr->scriptDirs, "added script dirs");
        $output->aList($files, "founded script files(total:$count$appendTitle)");
    }

    /**
     * @param Output $output
     * @param string $name
     */
    private function listScripts(Output $output, string $name): void
    {
        $listOpt = [
            'ucFirst'    => false,
            'ucTitleWords' => false,
            'filterEmpty' => true,
        ];

        if ($name) {
            if ($this->sr->isScriptName($name)) {
                $desc = '';
                $item = $this->sr->getScript($name);

                // [_meta => [desc, ]]
                if (is_array($item) && isset($item['_meta'])) {
                    $meta = $item['_meta'];
                    unset($item['_meta']);

                    $desc = $meta['desc'] ?? '';
                    // $output->colored(ucfirst($desc), 'cyan');
                }

                $output->aList([
                    'name'    => $name,
                    'desc'    => $desc,
                    'command' => $item,
                ], "Script: $name", $listOpt);
            } else {
                $matched = $this->sr->searchScripts($name);
                $number = \count($matched);
                $output->aList($matched, "'$name' matched scripts(number: $number)", $listOpt);
            }
        } else {
            $count = $this->sr->getScriptCount();
            $output->aList($this->sr->getScripts(), "registered scripts(total: $count)", $listOpt);
        }
    }

    /**
     * @param Output $output
     * @param string $kw
     */
    private function searchScripts(Output $output, string $kw): void
    {
        $matched = $this->sr->searchScripts($kw);

        $count = count($matched);
        if ($count === 0) {
            $output->info(':( not found matched commands by keywords: ' . $kw);
            return;
        }

        $listOpt = [
            'ucFirst' => false,
        ];
        $output->aList($matched, "matched scripts(total:$count, keyword:$kw)", $listOpt);
    }
}
