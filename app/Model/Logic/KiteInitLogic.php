<?php declare(strict_types=1);

namespace Inhere\Kite\Model\Logic;

use Inhere\Console\IO\Input;
use Inhere\Kite\Kite;
use Toolkit\Stdlib\Obj\AbstractObj;
use Toolkit\Sys\Util\ShellUtil;

/**
 * Class KiteInitLogic
 *
 * @package Inhere\Kite\Model\Logic
 */
class KiteInitLogic extends AbstractObj
{
    /**
     * @var bool
     */
    public $dryRun = false;

    /**
     * @var string
     */
    public $workDir = '';

    /**
     * @var string
     */
    public $kiteDir = '';

    /**
     */
    public function initConfig(): void
    {
        $app = Kite::cliApp();

        if ($this->dryRun) {
            $app->colored('DRY-RUN: install config OK', 'cyan');
            return;
        }

        $app->colored('INIT: install config OK', 'mga');
    }

    /**
     */
    public function installCompleter(string $genFile, string $shellName = '', string $tplFile = ''): void
    {
        if (!$shellName) {
            $shellName = ShellUtil::getName(true);
        }

        // build arg string.
        $args = [
            'kite',
            '--auto-completion' => true,
            '--shell-env'       => $shellName,
            '--gen-file'       => $genFile,
            // '--tpl-file'       => $tplFile,
        ];

        if ($tplFile) {
            $args['--tpl-file'] = $tplFile;
        }

        $app = Kite::cliApp();
        if ($this->dryRun) {
            $app->colored('DRY-RUN: install completer OK', 'cyan');
            return;
        }

        $in = new Input\ArrayInput($args);

        $app->runWithIO($in, $app->getOutput());
        $app->colored('INIT: install config OK', 'mga');
    }

    /**
     * @return string[]
     */
    public function getResult(): array
    {
        return  [
            'status' => 'OK'
        ];
    }

    /**
     * @param bool $dryRun
     */
    public function setDryRun(bool $dryRun): void
    {
        $this->dryRun = $dryRun;
    }
}
