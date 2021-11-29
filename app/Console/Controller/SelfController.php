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
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\OS;
use Toolkit\Stdlib\Php;
use Toolkit\Stdlib\Util\PhpDotEnv;
use Toolkit\Sys\Sys;
use Toolkit\Sys\Util\ShellUtil;
use function array_keys;
use function array_merge;
use function count;
use function is_scalar;
use function stripos;
use const BASE_PATH;
use const PHP_VERSION;

/**
 * Class SelfController
 *
 * @package Inhere\Kite\Console\Controller
 */
class SelfController extends Controller
{
    protected static string $name = 'self';

    protected static string $desc = 'Operate and manage kite self commands';

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
            'webui'  => [
                'web',
                'webUI',
                'web-ui'
            ],
            'config' => [
                'conf'
            ],
            'object' => [
                'obj'
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
     * show the kite application information
     *
     * @param Input  $input
     * @param Output $output
     */
    public function infoCommand(Input $input, Output $output): void
    {
        $app  = $this->getApp();
        $conf = $app->getConfig();

        $output->mList([
            'kite'   => [
                'root dir'     => $conf['rootPath'],
                'work dir'     => $input->getWorkDir(),
                'script count' => count($conf['scripts']),
                'plugin dirs'  => Kite::plugManager()->getPluginDirs(),
                '.env files'   => PhpDotEnv::global()->getLoadedFiles(),
                'config files' => $conf['__loaded_file'],
            ],
            'system' => [
                'OS name'     => OS::name(),
                'shell env'   => ShellUtil::getName(true),
                'PHP version' => PHP_VERSION,
                'home dir'    => OS::getUserHomeDir(),
            ]
        ]);
    }

    /**
     * get the kite paths
     *
     * @options
     *  --inline    bool;Output without newline.
     *
     * @arguments
     *  path        The sub-path in the kite. if empty, return kite path.
     *
     * @example
     *  {binWithCmd}
     *  {binWithCmd} tmp/logs/some.log
     *  {binWithCmd} tmp/logs/some.log --inline
     *
     * @param Input  $input
     * @param Output $output
     */
    public function pathCommand(FlagsParser $fs, Output $output): void
    {
        $subPath = $fs->getArg(0);
        $fullPath = Kite::getPath($subPath);

        if ($fs->getOpt('inline')) {
            $output->writeRaw($fullPath);
            return;
        }

        $output->println($fullPath);
    }

    /**
     * show the application config information
     *
     * @options
     *  -s, --search         Search config names by `key` argument
     *      --keys           bool;Only show all key names of config
     *      --clean          bool;Output clean value.
     *
     * @arguments
     *  key     The key of config
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function configCommand(FlagsParser $fs, Output $output): void
    {
        $app = $this->getApp();
        if ($fs->getOpt('keys')) {
            $output->aList(array_keys($app->getConfig()), 'Keys of config');
            return;
        }

        $conf = $app->getConfig();
        $key  = $fs->getArg(0);

        // show all config
        if (!$key) {
            $output->title('Application Config', [
                'indent'   => 0,
                'titlePos' => Title::POS_MIDDLE,
            ]);
            $output->json($conf);
            return;
        }

        $value = null;
        $match = $fs->getOpt('search');
        if ($match) { // match key
            foreach ($conf as $name => $item) {
                if (stripos($name, $key) !== false) {
                    $value[$name] = $item;
                }
            }
        } elseif (isset($conf[$key])) {
            $value = $conf[$key];
        }

        if ($value === null) {
            throw new PromptException("config key '$key' is not exists");
        }

        if (is_scalar($value)) {
            $output->info("Config '$key' Value: $value");
        } else {
            $output->title("Config: '$key'", [
                'indent'   => 0,
                'titlePos' => Title::POS_MIDDLE,
            ]);
            $output->json($value);
        }
    }

    /**
     * Show container objects information on the kite
     *
     * @arguments
     *  objectName      Show an object info in the kite
     *
     * @options
     *   -l, --list      List all registered object names
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function objectCommand(FlagsParser $fs, Output $output): void
    {
        $box = Kite::box();
        if ($fs->getOpt('l,list')) {
            $output->aList($box->getObjectIds(), 'Registered IDs');
            return;
        }

        $objName = $fs->getArg('objectName');
        if (!$objName) {
            $output->liteError("Please input an object ID/name for see detail");
            return;
        }

        if (!$box->has($objName)) {
            $output->liteError("Object '$objName' not found in kite");
        } else {
            echo Php::dumpVars($box->get($objName));
        }
    }

    /**
     * update {binName} to latest from github repository(by git pull)
     *
     * @options
     *  --no-deps     bool;Not update deps by composer update
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function updateCommand(FlagsParser $fs, Output $output): void
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

        if (!$fs->getOpt('no-deps')) {
            Color::println('Run composer update:');
            $cmd = 'composer update';
            // [, $msg,] = Sys::run($cmd);
            // $output->writeln($msg);
            SysCmd::quickExec($cmd, $dir);
        }

        Color::println('Add execute perm:');
        $binName = $this->input->getScriptName();

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
        'root'    => '',
        // 'entry'     => 'public/index.php',
        'entry'   => '',
        // 'php-bin'  => 'php'
        'php-bin' => '',
        // address
        'addr'    => '127.0.0.1:8552',
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
     *  -s, -S, --addr     The http server address. e.g 127.0.0.1:8552
     *  -b, --php-bin      The php binary file(<comment>php</comment>)
     *      --show-info    bool;Only show serve info, not start listen
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @throws Throwable
     */
    public function webuiCommand(FlagsParser $fs, Output $output): void
    {
        $this->webUi = array_merge($this->webUi, $this->app->getArrayParam('webui'));
        // vdump(BASE_PATH, $this->webUi);

        $svrAddr = $fs->getOpt('addr', $this->webUi['addr']);
        $phpBin  = $fs->getOpt('php-bin', $this->webUi['php-bin']);

        $docRoot = $this->webUi['root'];
        // $pds = PhpDevServe::new($svrAddr, 'public', 'public/index.php');
        $pds = PhpDevServe::new($svrAddr, $docRoot);
        $pds->setPhpBin($phpBin);

        if ($fs->getOpt('show-info')) {
            $output->aList($pds->getInfo(), 'Listen Information', ['ucFirst' => false]);
            return;
        }

        $pds->listen();
    }
}
