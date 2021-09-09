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
use Inhere\Kite\Component\ScriptRunner;
use Inhere\Kite\Kite;
use function count;
use function is_array;

/**
 * Class RunCommand
 */
class RunCommand extends Command
{
    protected static $name = 'run';

    protected static $description = 'run an script command or script file';

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
     *  -l, --list TYPE     List information for all scripts or script files. type: file, cmd(default)
     *  -s, --search        Display all matched scripts by the input name
     *      --info          Show information for give script name or file
     *      --dry-run       Mock running an script
     *      --proxy         Enable proxy ENV setting
     *
     * @arguments
     *  name        The script name for execute
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *   {binWithCmd} hello.sh one two three 'a b c'
     */
    protected function execute($input, $output)
    {
        $name = $input->getFirstArg();

        $listType = $input->getSameStringOpt('l, list');
        if ($listType === ScriptRunner::TYPE_FILE) {
            $this->listScriptFiles($output, $name);
            return;
        }

        // support search
        $kw = $input->getSameStringOpt(['s', 'search']) ?: $name;
        if ($input->hasOneOpt(['s', 'search'])) {
            $this->searchScripts($output, $kw);
            return;
        }

        // default list script commands
        if ($listType) {
            $this->listScripts($output, $name);
            return;
        }

        if (!$name) {
            $output->liteError('please input an script name for run or use -l TYPE see all scripts');
            return;
        }

        $runArgs = $input->getArguments();
        unset($runArgs[0]); // first is script name

        $dryRun = $input->getBoolOpt('dry-run');
        $this->sr->setDryRun($dryRun);

        // not found script name
        if (!$this->sr->isScriptName($name)) {
            if ($scriptFile = $this->sr->findScriptFile($name)) {
                $this->sr->runScriptFile($scriptFile, $runArgs);
                return;
            }

            $output->liteError("please input an exists script name for run. ('$name' not exists)");
            return;
        }

        // run script by name
        $this->sr->runCustomScript($name, $runArgs);
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

        $output->aList($this->sr->getScriptDirs(), "added script dirs");
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
