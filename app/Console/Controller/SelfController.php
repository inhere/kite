<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Controller;

use Inhere\Console\Console;
use Inhere\Console\Controller;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Toolkit\Cli\Color;
use Toolkit\Sys\Sys;
use function count;

/**
 * Class SelfController
 *
 * @package Inhere\Kite\Console\Controller
 */
class SelfController extends Controller
{
    protected static $name = 'self';

    protected static $description = 'Operate Kite self commands';

    /**
     * @var string
     */
    protected $baseDir;

    /**
     * @var string
     */
    protected $repoDir;

    protected static function commandAliases(): array
    {
        return [
            'up' => 'update',
        ];
    }

    protected function init(): void
    {
        parent::init();

        $this->baseDir = BASE_PATH;
        $this->repoDir = $this->input->getPwd();
    }

    /**
     * show the application information
     *
     * @param Input  $input
     * @param Output $output
     */
    public function infoCommand(Input $input, Output $output): void
    {
        $app  = $this->getApp();
        $conf = $app->getConfig();

        $output->aList([
            'work path' => $this->repoDir,
            'root path' => $conf['rootPath'],
            'loaded file' => $conf['__loaded_file'],
            'script count' => count($conf['scripts']),
        ], 'information');
    }

    /**
     * update {binName} to latest from github repository(by git pull)
     *
     * @param Input  $input
     * @param Output $output
     */
    public function updateCommand(Input $input, Output $output): void
    {
        Color::println('Update to latest:');

        $cmd = "cd {$this->baseDir} && git checkout . && git pull";
        [, $msg,] = Sys::run($cmd);

        $output->writeln($msg);

        Color::println('Add execute perm:');

        $binName = $input->getScriptName();

        // normal run
        [$code, $msg,] = Sys::run("cd {$this->baseDir} && chmod a+x bin/$binName");
        if ($code !== 0) {
            $output->error($msg);
            return;
        }

        $output->success('Complete');
    }
}
