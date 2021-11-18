<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Controller;

use Exception;
use Inhere\Console\Controller;
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Output;
use Inhere\Console\Util\PhpDevServe;
use Inhere\Kite\Common\Cmd;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Common\GitLocal\GitHub;
use Inhere\Kite\Console\Component\Clipboard;
use Inhere\Kite\Console\Component\ContentsAutoReader;
use Inhere\Kite\Helper\AppHelper;
use Inhere\Kite\Helper\KiteUtil;
use InvalidArgumentException;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Json;
use Toolkit\Stdlib\Php;
use Toolkit\Sys\Sys;
use function array_filter;
use function array_merge;
use function dirname;
use function function_exists;
use function implode;
use function is_dir;
use function is_file;
use function is_numeric;
use function ob_get_clean;
use function ob_start;
use function preg_quote;
use function sprintf;
use function str_contains;
use function strlen;
use function trim;
use function vdump;

/**
 * Class GitGroup
 * - php:cs-fix   add tag and push to remote
 * - php:lint delete the tag on remote
 *
 */
class PhpController extends Controller
{
    protected static $name = 'php';

    protected static $desc = 'Some useful tool commands for php development';

    protected static function commandAliases(): array
    {
        return [
            'csFix'   => ['csfix', 'cs-fix'],
            'ghPkg'   => ['ghpkg'],
            'pkgNew'  => ['pkgnew', 'pkg-new', 'create'],
            'pkgOpen' => ['open', 'pkg-open'],
            'runCode' => ['eval', 'run-code', 'run-codes'],
            'runFunc' => ['run', 'exec', 'run-func'],
            'runUnit' => ['run-unit', 'unit', 'run-test', 'phpunit'],
        ];
    }

    /**
     * convert input string to PHP array.
     *
     * @options
     *  --cb            bool;read source code from clipboard
     *  -f, --file      The source code file
     *  -s, --sep       The sep char for split.
     *  -o, --output    The output target. default is stdout.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function str2arrCommand(FlagsParser $fs, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * convert create mysql table SQL to PHP class
     *
     * @options
     *  -s,--source     The source code string or file
     *  -o,--output     The output target. default is stdout.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function text2classCommand(FlagsParser $fs, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * convert create mysql table SQL to PHP class
     *
     * @options
     *  -s,--source     The source code string or file
     *  -o,--output     The output target. default is stdout.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function arr2classCommand(FlagsParser $fs, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * convert create mysql table SQL to PHP class
     *
     * @options
     *  -s,--source     The source code string or file
     *  -o,--output     The output target. default is stdout.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function json2classCommand(FlagsParser $fs, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * convert create mysql table SQL to PHP class
     *
     * @options
     *  -s,--source     The source code string or file
     *  -o,--output     The output target. default is stdout.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function sql2classCommand(FlagsParser $fs, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * convert an mysql INSERT SQL to php k-v array
     *
     * @options
     *  -s,--source     The source code string or file
     *  -o,--output     The output target. default is stdout.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function sql2arrCommand(FlagsParser $fs, Output $output): void
    {
        $output->success('Complete');
    }

    /**
     * auto find the phpunit.xml dir and run phpunit tests
     *
     * @arguments
     *  dir         The php unit tests code dir or file path
     *
     * @options
     *      --no-debug    bool;not set the --debug option on run test
     *  -f, --filter      Set keywords for the --filter option
     *      --php-bin     manual set the php bin  file path.
     *      --phpunit     manual set the phpunit(.phar)  file path.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function runUnitCommand(FlagsParser $fs, Output $output): void
    {
        $dir = $fs->getArg('dir', $this->getInput()->getWorkDir());
        if (is_file($dir)) {
            $dir = dirname($dir);
        }

        $runDir = KiteUtil::findPhpUnitConfigFile($dir);
        if (!$runDir) {
            throw new InvalidArgumentException("not found the phpunit.xml(.dist) in $dir or any parent dir");
        }

        $output->info('found the phpunit config dir: ' . $runDir);

        // phpunit --debug --filter KEYWORDS
        $cmd = Cmd::new('phpunit')->setWorkDir($runDir);
        $cmd->addIf('--debug', !$fs->getOpt('no-debug'));

        if ($filter = $fs->getOpt('filter')) {
            $cmd->addArgs('--filter', $filter);
        }

        $cmd->runAndPrint();

        $output->success('Complete');
    }

    /**
     * run php-cs-fixer for an dir, and auto add git commit message
     *
     * @options
     *  --not-commit    bool;Dont run `git add` and `git commit` commands
     *
     * @arguments
     *  directory  string;The directory for run php-cs-fixer;required
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @example
     *  {binWithCmd} src/rpc-client
     */
    public function csFixCommand(FlagsParser $fs, Output $output): void
    {
        $dir = $fs->getArg('directory');
        if (!is_dir($dir)) {
            $output->error('please input an exists directory. current: ' . $dir);
            return;
        }

        [$code, $outMsg,] = Sys::run("php-cs-fixer fix $dir");

        echo $outMsg, "\n";

        if ($code === 0) {
            $gitCommand = "git add . && git commit -m \"up: format codes by run php-cs-fixer for $dir\"";
            $output->colored('> ' . $gitCommand, 'comment');

            [$code1, $outMsg,] = Sys::run($gitCommand);

            echo $outMsg, "\n";

            if ($code1 === 0) {
                $output->success('OK');
            }
        }
    }

    public const DEF_SERVE_CONF = [
        'hce-file' => '',
        'hce-env'  => '',
        // document root
        // 'root'     => 'public',
        'root'     => '',
        // 'entry'     => 'public/index.php',
        'entry'    => '',
        // 'php-bin'  => 'php'
        'php-bin'  => '',
        // 'addr' => '127.0.0.1:8552',
        'addr'     => '',
        'envVars'  => [],
    ];

    /**
     * start a php built-in http server for development
     *
     * @usage
     *  {binWithCmd} [-S HOST]
     *  {binWithCmd} [-S HOST:PORT]
     *  {binWithCmd} [-S :PORT] [entry file]
     *
     * @options
     *  -s, -S, --addr    The http server address. e.g 127.0.0.1:8552
     *  -t, --doc-root    The document root dir for server(<comment>public</comment>)
     *  -b, --php-bin     The php binary file(<comment>php</comment>)
     *      --hce-file    The IDEA http client env file
     *      --hce-env     The current http client env name
     *      --show-info   bool;Only show serve info, not start listen
     *
     * @arguments
     *  file         The entry file for server. e.g web/index.php
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @throws Exception
     * @example
     *  {binWithCmd} -s 127.0.0.1:8552 web/index.php
     *  {binWithCmd} --hce-file test/clienttest/http-client.env.json
     *  {binWithCmd} --hce-file test/clienttest/http-client.env.json --hce-env development
     */
    public function serveCommand(FlagsParser $fs, Output $output): void
    {
        $conf = $this->app->getArrayParam('php_serve');
        if ($conf) {
            $conf = array_merge(self::DEF_SERVE_CONF, $conf);

            // print config
            if ($appConf = array_filter($conf)) {
                $output->aList($appConf, 'Config Information', [
                    'ucFirst' => false,
                ]);
            }
        } else {
            $conf = self::DEF_SERVE_CONF;
        }

        $hceFile = $fs->getOpt('hce-file', $conf['hce-file']);
        $hceEnv  = $fs->getOpt('hce-env', $conf['hce-env']);
        $phpBin  = $fs->getOpt('php-bin', $conf['php-bin']);
        $docRoot = $fs->getOpt('doc-root', $conf['root']);

        $entryFile = $fs->getArg('file', $conf['entry']);
        $serveAddr = $fs->getOpt('addr', $conf['addr']);

        $pds = PhpDevServe::new($serveAddr, $docRoot, $entryFile);
        $pds->setPhpBin($phpBin);
        $pds->setEnvVars($conf['envVars'] ?? []);

        // \vdump($hceEnv , $hceFile);
        if ($hceEnv && $hceFile) {
            $loaded = $pds->loadHceFile($hceFile);
            if ($loaded) {
                $output->info('the http client env file is loaded');
                $pds->useHceEnv($hceEnv);
            } else {
                $output->liteWarning('the http client env file is not exists');
            }
        }

        if ($fs->getOpt('show-info')) {
            $output->aList($pds->getInfo(), 'Listen Information', ['ucFirst' => false]);
            return;
        }

        $pds->listen();
    }

    /**
     * exec php code snippets and dump results.
     *
     * @arguments
     *  funcName       string;The php function name;true
     *  funcArgs       array;the function args, allow multi args
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @example
     *  {binWithCmd} strlen "inhere" # output: 6
     *  {binWithCmd} basename refs/heads/master # output: master
     *
     */
    public function runFuncCommand(FlagsParser $fs, Output $output): void
    {
        $funcName = $fs->getArg('funcName');
        $funcAlias = [
            'len' => 'strlen',
        ];

        $funcName = $funcAlias[$funcName] ?? $funcName;
        if (!$funcName || !function_exists($funcName)) {
            throw new InvalidArgumentException("input '$funcName' is not exists");
        }

        /** @var array $args */
        if ($args = $fs->getArg('funcArgs')) {
            $fmtArgs = [];
            foreach ($args as $k => $arg) {
                if (is_numeric($arg) && strlen($arg) < 11) {
                    $fmtArgs[] = $arg;
                    $args[$k]  = (int)$arg;
                } else {
                    $fmtArgs[] = str_contains($arg, '"') ? "'$arg'" : '"' . $arg . '"';
                }
            }

            $ret = $funcName(...$args);
            $str = sprintf('%s(%s);', $funcName, implode(', ', $fmtArgs));
        } else {
            $ret = $funcName();
            $str = $funcName . '();';
        }

        $output->colored('Code:');
        $output->colored("  $str", 'code');
        $output->colored('RESULT:');
        echo Php::dumpVars($ret);
    }

    /**
     * exec php code snippets and dump results.
     *
     * @arguments
     *  code       The php codes, not need php start tag.
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @example
     *  {binWithCmd} 'strlen("inhere")' # output: 6
     *
     */
    public function runCodeCommand(FlagsParser $fs, Output $output): void
    {
        $code = $fs->getArg('code');
        $code = ContentsAutoReader::readFrom($code);

        if (!$code) {
            throw new InvalidArgumentException('empty input codes for run');
        }

        $ret = $this->evalCode($code);

        $output->info('RESULT:');
        $output->writeRaw($ret);
    }

    /**
     * @param string $code
     *
     * @return string
     */
    private function evalCode(string $code): string
    {
        $code = rtrim(trim($code), ';');

        $phpCode = <<<CODE
use Toolkit\Stdlib\Php;

// run
echo Php::dumpVars($code);
CODE;

        ob_start();
        eval($phpCode);
        return ob_get_clean();
    }

    /**
     * open the php doc sites
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function docOpenCommand(FlagsParser $fs, Output $output): void
    {
        $output->info('TODO');
    }

    /**
     * Search php package from packagist.org
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function pkgSearchCommand(FlagsParser $fs, Output $output): void
    {
        $output->info('TODO');
    }

    /**
     * open php package page on packagist.org
     *
     * @arguments
     *  name        string;The package name, eg: inhere/console;required
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @example
     * {binWithCmd} inhere/console # will open https://packagist.org/packages/inhere/console
     */
    public function pkgOpenCommand(FlagsParser $fs, Output $output): void
    {
        $pkgName = $fs->getArg('name');
        $pageUrl = "https://packagist.org/packages/$pkgName";

        AppHelper::openBrowser($pageUrl);
        $output->info('TODO');
    }

    /**
     * Create new php package from a github template repo
     *
     * @arguments
     *  name        string;The new package name;required
     *
     * @options
     *  --tpl-repo      The template repo path or url on the github. default: inherelab/php-pkg-template
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function pkgNewCommand(FlagsParser $fs, Output $output): void
    {
        $pkgName  = $fs->getArg('name');
        $repoPath = $fs->getOpt('tpl-repo', 'inherelab/php-pkg-template');

        Cmd::new('git')
            ->add('clone')
            ->addf('%s/%s', GitHub::GITHUB_HOST, $repoPath)
            ->add($pkgName)
            ->runAndPrint();

        $output->success('Completed');
    }

    /**
     * Replace the local package use github repository codes
     *
     * @arguments
     *  pkgName     string;The package name. eg: inhere/console;required
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @example
     *  {binWithCmd} inhere/console
     *  {binWithCmd} vendor/inhere/console
     */
    public function ghPkgCommand(FlagsParser $fs, Output $output): void
    {
        $pkgPath = $pkgName = $fs->getArg('pkgName');

        // an dirname
        if (!is_dir($pkgPath)) {
            $pkgPath = 'vendor/' . $pkgName;
            if (!is_dir($pkgPath)) {
                throw new PromptException("package path '$pkgPath' is not exists");
            }
        }

        $composerJson = $pkgPath . '/composer.json';
        $composerInfo = Json::decodeFile($composerJson, true);

        if (!empty($composerInfo['name'])) {
            $pkgName = $composerInfo['name'];
        }

        $homepage = GitHub::GITHUB_HOST . "/$pkgName";
        if (!empty($composerInfo['homepage'])) {
            $homepage = $composerInfo['homepage'];
        }

        $output->aList([
            'pkgName' => $pkgName,
            'pkgPath' => $pkgPath,
            'pkgJson' => $composerJson,
            'github'  => $homepage,
        ], 'information', ['ucFirst' => false]);

        if ($this->unConfirm('continue')) {
            $output->colored('  GoodBye');
            return;
        }

        CmdRunner::new('rm -rf ' . $pkgPath)
            ->do(true)
            ->afterOkDo("git clone $homepage $pkgPath");
    }
}
