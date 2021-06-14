<?php declare(strict_types=1);

namespace Inhere\Kite\Console\Controller;

use Inhere\Console\Component\Formatter\Title;
use Inhere\Console\Controller;
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Console\Util\PhpDevServe;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Helper\SysCmd;
use Inhere\Kite\Kite;
use Throwable;
use Toolkit\Cli\Color;
use Toolkit\Sys\Sys;
use function array_keys;
use function array_merge;
use function count;
use function is_scalar;
use function vdump;
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
            'webui'    => [
                'web', 'webUI', 'web-ui'
            ],
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
            'config files' => $conf['__loaded_file'],
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

    /**
     * [
     *  'addr' => '127.0.0.1:8090',
     * ]
     *
     * @var array
     */
    protected $webUi = [
        // document root
        // 'root'     => 'public',
        'root'     => '',
        // 'entry'     => 'public/index.php',
        'entry'    => '',
        // 'php-bin'  => 'php'
        'php-bin'  => '',
        // address
        'addr' => '127.0.0.1:8552',
    ];

    /**
     * start the kite web UI server
     *
     * @usage
     *  {binWithCmd} [-S HOST]
     *  {binWithCmd} [-S :PORT]
     *  {binWithCmd} [-S HOST:PORT]
     *
     * @options
     *  -s, -S, --addr STRING    The http server address. e.g 127.0.0.1:8552
     *  -b, --php-bin STRING     The php binary file(<comment>php</comment>)
     *      --show-info          Only show serve info, not start listen
     *
     * @param Input  $input
     * @param Output $output
     *
     * @throws Throwable
     */
    public function webuiCommand(Input $input, Output $output): void
    {
        $this->webUi = array_merge($this->webUi, $this->app->getParam('webui', []));
        vdump(BASE_PATH, $this->webUi);

        $svrAddr = $input->getSameStringOpt('s,S,addr', $this->webUi['addr']);
        $phpBin  = $input->getStringOpt('php-bin', $this->webUi['php-bin']);
        // $docRoot = $input->getSameStringOpt('t,doc-root', $conf['root']);

        $docRoot = $this->webUi['root'];
        // $pds = PhpDevServe::new($svrAddr, 'public', 'public/index.php');
        $pds = PhpDevServe::new($svrAddr, $docRoot);
        $pds->setPhpBin($phpBin);

        if ($input->getBoolOpt('show-info')) {
            $output->aList($pds->getInfo(), 'Listen Information', ['ucFirst' => false]);
            return;
        }

        $pds->listen();
    }
}
