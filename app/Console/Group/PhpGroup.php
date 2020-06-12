<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Group;

use Inhere\Console\Controller;
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Common\GitLocal\GitHub;
use Toolkit\Stdlib\Json;
use Toolkit\Sys\Sys;
use function is_dir;

/**
 * Class GitGroup
 * - php:cs-fix   add tag and push to remote
 * - php:lint delete the tag on remote
 *
 */
class PhpGroup extends Controller
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

    /**
     * run a php built-in server for development(is alias of the command 'server:dev')
     *
     * @usage
     *  {command} [-S HOST:PORT]
     *  {command} [-H HOST] [-p PORT]
     * @options
     *  -S         The server address. e.g 127.0.0.1:5577
     *  -H,--host  The server host address. e.g 127.0.0.1
     *  -p,--port  The server host address. e.g 5577
     */
    public function sync2Command(): void
    {
        echo "string\n";
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
     * @example
     *  {binWithCmd} inhere/console
     */
    public function ghPkgCommand(Input $input, Output $output): void
    {
        $pkgName = $input->getRequiredArg('pkgName');
        $pkgPath = 'vendor/' . $pkgName;

        if (!is_dir($pkgPath)) {
            throw new PromptException("package path '{$pkgPath}' is not exists");
        }

        $composerJson = $pkgPath . '/composer.json';
        $composerInfo = Json::decodeFile($composerJson, true);

        $homepage = GitHub::GITHUB_HOST . "/$pkgName";
        if (!empty($composerInfo['homepage'])) {
            $homepage = $composerInfo['homepage'];
        }

        $output->aList([
            'pkgName' => $pkgName,
            'pkgPath' => $pkgPath,
            'pkgJson' => $composerJson,
        ], 'information', ['ucFirst' => false]);

        if ($this->unConfirm('continue')) {
            $output->colored('GoodBye');
            return;
        }

        CmdRunner::new('rm -rf ' . $pkgPath)
                 ->do(true)
                 ->afterOkRun("git clone $homepage $pkgPath");
    }
}
