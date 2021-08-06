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

        $in = new Input\ArrayInput($args);

        $app = Kite::cliApp();
        $app->runWithIO($in, $app->getOutput());
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
}
