<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Controller;

use Inhere\Console\Component\Formatter\Title;
use Inhere\Console\Controller;
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Helper\SysCmd;
use Inhere\Kite\Kite;
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

    protected static $description = 'Operate and manage kite self commands';

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
            'work dir'     => $input->getWorkDir(),
            'root path'    => $conf['rootPath'],
            'script count' => count($conf['scripts']),
            'plugin dirs'  => Kite::plugManager()->getPluginDirs(),
            'config files'  => $conf['__loaded_file'],
        ], 'information');
    }

    /**
     * show the application config information
     *
     * @options
     *  --keys     Only show all key names of config
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
        if ($input->getBoolOpt('keys')) {
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
                $output->info("Config '$key' Value: $val");
            } else {
                $output->title("Config: '$key'", [
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
     * @options
     *  --no-deps     Not update deps by composer update
     *
     * @param Input  $input
     * @param Output $output
     */
    public function updateCommand(Input $input, Output $output): void
    {
        $dir = $this->baseDir;
        $output->info('Will change to kite directory: ' . $dir);

        if (AppHelper::isInPhar()) {
            throw new PromptException('kite is phar package, does not support upgrade via command yet');
        }

        Color::println('Update to latest:');
        $cmd = "git checkout . && git pull";
        // [, $msg,] = Sys::run($cmd);
        SysCmd::quickExec($cmd, $dir);

        if (!$input->getBoolOpt('no-deps')) {
            Color::println('Run composer update:');
            $cmd = 'composer update';
            // [, $msg,] = Sys::run($cmd);
            // $output->writeln($msg);
            SysCmd::quickExec($cmd, $dir);
        }

        Color::println('Add execute perm:');
        $binName = $input->getScriptName();

        // normal run
        [$code, $msg,] = Sys::run("chmod a+x bin/$binName", $dir);
        if ($code !== 0) {
            $output->error($msg);
            return;
        }

        $output->success('Complete');
    }
}
