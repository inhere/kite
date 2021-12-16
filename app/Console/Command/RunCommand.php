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
    private $sr;

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
     *  -l, --list          List information for all scripts or script files. type: file, cmd(default)
     *  -s, --search        Display all matched scripts by the input name
     *      --info          Show information for give script name or file
     *      --dry-run       bool;Mock running, not real execute.
     *      --proxy         bool;Enable proxy ENV setting
     *
     * @arguments
     *  name        The script/plugin name for execute.
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *   {binWithCmd} hello.sh one two three 'a b c'
     */
    protected function execute(Input $input, Output $output)
    {
        $name = $this->flags->getArg('name');
        $output->info('workdir: ' . $input->getWorkDir());

        $listType = $this->flags->getOpt('list');
        if ($listType === ScriptRunner::TYPE_FILE) {
            $this->listScriptFiles($output, $name);
            return;
        }

        // support search
        $kw = $this->flags->getOpt('search') ?: $name;
        if ($this->flags->hasInputArg('search')) {
            $this->searchScripts($output, $kw);
            return;
        }

        // default list script commands
        if ($listType) {
            $this->listScripts($output, $name);
            return;
        }

        if (!$name) {
            $output->liteError('please input an name for run or use -l TYPE see all scripts');
            return;
        }

        $dryRun = $this->flags->getOpt('dry-run');
        $this->sr->setDryRun($dryRun);

        // proxy
        $openProxy = $this->flags->getOpt('proxy');
        $proxyEnv  = $this->app->getArrayParam('proxyEnv');
        if ($openProxy && $proxyEnv) {
            Show::aList($proxyEnv, 'Set Proxy ENV From Config: "proxyEnv"', [
                'ucFirst'      => false,
                'ucTitleWords' => false,
            ]);

            OS::setEnvVars($proxyEnv);
        }

        // $runArgs = $this->flags->getRawArgs(); // 会包含 arg: name
        $runArgs = $this->flags->getRemainArgs();

        if (!$this->sr->isScriptName($name)) {
            // - found script file
            if ($scriptFile = $this->sr->findScriptFile($name)) {
                $this->sr->runScriptFile($scriptFile, $runArgs);
                return;
            }

            // - is an plugin
            if (Kite::plugManager()->isPlugin($name)) {
                $output->notice("input is an plugin name, will run plugin: $name");
                Kite::plugManager()->run($name, $this->app, $runArgs);
                return;
            }

            // as script expr and run.
            $this->sr->runInputScript($name);
            // $output->liteError("please input an exists script name for run. ('$name' not exists)");
            return;
        }

        // run script by name
        $this->sr->runScriptByName($name, $runArgs);
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
            'ucFirst' => false,
        ];

        if ($name && $this->sr->isScriptName($name)) {
            $item = $this->sr->getScript($name);

            // [_meta => [desc, ]]
            if (is_array($item) && isset($item['_meta'])) {
                $meta = $item['_meta'];
                unset($item['_meta']);

                $desc = $meta['desc'] ?? '';
                if ($desc) {
                    $output->colored($desc . "\n", 'cyan');
                }
            }

            $output->aList([
                'name'    => $name,
                'command' => $item,
            ], 'script information', $listOpt);
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
        // search
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
