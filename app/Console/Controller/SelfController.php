<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Controller;

use Inhere\Console\Component\Formatter\Title;
use Inhere\Console\Controller;
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Console\Util\PhpDevServe;
use Toolkit\Cli\Color;
use Toolkit\Sys\Sys;
use function array_keys;
use function count;
use function is_scalar;
use const BASE_PATH;

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
            'up'     => 'update',
            'web'    => 'serve',
            'config' => [
                'conf'
            ],
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
            'work dir'     => $this->repoDir,
            'root path'    => $conf['rootPath'],
            'loaded file'  => $conf['__loaded_file'],
            'script count' => count($conf['scripts']),
        ], 'information');
    }

    /**
     * show the application config information
     *
     * @options
     *  --show-keys     Only show all key names of config
     *
     * @arguments
     *  key     The key of config
     *
     * @param Input  $input
     * @param Output $output
     */
    public function configCommand(Input $input, Output $output): void
    {
        $app = $this->getApp();
        if ($input->getBoolOpt('show-keys')) {
            $output->aList(array_keys($app->getConfig()), 'Keys of config');
            return;
        }

        $key = $input->getStringArg(0);

        if ($key) {
            $val = $app->getParam($key);
            if ($val === null) {
                throw new PromptException("config key '{$key}' is not exists");
            }

            if (is_scalar($val)) {
                $output->info("$key Value: $val");
            } else {
                $output->title("'$key' Value", [
                    'indent'   => 0,
                    'titlePos' => Title::POS_MIDDLE,
                ]);
                $output->json($val);
            }
            return;
        }

        $conf = $app->getConfig();
        $output->title('Application Config', [
            'indent'   => 0,
            'titlePos' => Title::POS_MIDDLE,
        ]);
        $output->json($conf);
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

    /**
     * start a php built-in http server for kite web application
     *
     * @param Input  $input
     * @param Output $output
     *
     * @return int|mixed|void
     * @example
     *  {command} -S 127.0.0.1:8552 web/index.php
     */
    public function serveCommand(Input $input, Output $output): void
    {
        $conf = $this->app->getParam('webServe', []);
        if (!$conf) {
            throw new PromptException('please config the "webServe" settings');
        }

        $docRoot = $conf['root'] ?? BASE_PATH . '/public';

        $serveAddr = $conf['host'] ?? '127.0.0.1:8552';
        $entryFile = $conf['entry'] ?? '';

        $pds = PhpDevServe::new($serveAddr, $docRoot, $entryFile);

        $output->write($pds->getTipsMessage());

        $pds->start();
    }
}
