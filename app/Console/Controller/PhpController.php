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
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Console\Util\PhpDevServe;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Common\GitLocal\GitHub;
use Toolkit\Stdlib\Json;
use Toolkit\Sys\Sys;
use function array_filter;
use function array_merge;
use function is_dir;

/**
 * Class GitGroup
 * - php:cs-fix   add tag and push to remote
 * - php:lint delete the tag on remote
 *
 */
class PhpController extends Controller
{
    protected static $name = 'php';

    protected static $description = 'Some useful tool commands for php development';

    protected static function commandAliases(): array
    {
        return [
            'csfix'  => 'csFix',
            'cs-fix' => 'csFix',
            'ghpkg'  => 'ghPkg'
        ];
    }

    /**
     * run php-cs-fixer for an dir, and auto add git commit message
     *
     * @options
     *  --not-commit  Dont run `git add` and `git commit` commands
     *
     * @arguments
     *  directory  The directory for run php-cs-fixer
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {binWithCmd} src/rpc-client
     */
    public function csFixCommand(Input $input, Output $output): void
    {
        $dir = $input->getRequiredArg(0);

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
     *  -s, -S, --addr STRING    The http server address. e.g 127.0.0.1:8552
     *  -t, --doc-root STRING    The document root dir for server(<comment>public</comment>)
     *  -b, --php-bin STRING     The php binary file(<comment>php</comment>)
     *      --hce-file STRING    The IDEA http client env file
     *      --hce-env STRING     The current http client env name
     *      --show-info          Only show serve info, not start listen
     *
     * @arguments
     *  file=STRING         The entry file for server. e.g web/index.php
     *
     * @param Input  $input
     * @param Output $output
     *
     * @throws Exception
     * @example
     *  {binWithCmd} -s 127.0.0.1:8552 web/index.php
     *  {binWithCmd} --hce-file test/clienttest/http-client.env.json
     *  {binWithCmd} --hce-file test/clienttest/http-client.env.json --hce-env development
     */
    public function serveCommand(Input $input, Output $output): void
    {
        $conf = $this->app->getParam('php:serve', []);
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

        $input->bindArgument('file', 0);

        $hceFile = $input->getStringOpt('hce-file', $conf['hce-file']);
        $hceEnv  = $input->getStringOpt('hce-env', $conf['hce-env']);
        $phpBin  = $input->getStringOpt('php-bin', $conf['php-bin']);
        $docRoot = $input->getSameStringOpt('t,doc-root', $conf['root']);

        $entryFile = $input->getStringArg('file', $conf['entry']);
        $serveAddr = $input->getSameStringOpt('s,S,addr', $conf['addr']);

        $pds = PhpDevServe::new($serveAddr, $docRoot, $entryFile);
        $pds->setPhpBin($phpBin);

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

        if ($input->getBoolOpt('show-info')) {
            $output->aList($pds->getInfo(), 'Listen Information', ['ucFirst' => false]);
            return;
        }

        $pds->listen();
    }

    /**
     * Search php package from packagist.org
     *
     * @param Input  $input
     * @param Output $output
     */
    public function pkgSearch(Input $input, Output $output): void
    {

    }

    /**
     * @param Input $input
     */
    protected function ghPkgConfigure(Input $input): void
    {
        $input->bindArguments(['pkgName' => 0]);
    }

    /**
     * Replace the local package use github repository codes
     *
     * @arguments
     *  pkgName     The package name. eg: inhere/console
     *
     * @param Input  $input
     * @param Output $output
     *
     * @example
     *  {binWithCmd} inhere/console
     *  {binWithCmd} vendor/inhere/console
     */
    public function ghPkgCommand(Input $input, Output $output): void
    {
        $pkgPath = $pkgName = $input->getRequiredArg('pkgName');

        // an dirname
        if (!is_dir($pkgPath)) {
            $pkgPath = 'vendor/' . $pkgName;
            if (!is_dir($pkgPath)) {
                throw new PromptException("package path '{$pkgPath}' is not exists");
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
