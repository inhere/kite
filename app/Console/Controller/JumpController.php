<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Controller;

use Inhere\Console\Controller;
use Inhere\Console\Exception\PromptException;
use Inhere\Console\IO\Output;
use Inhere\Kite\Kite;
use Inhere\Kite\Lib\Jump\JumpShell;
use Inhere\Kite\Lib\Jump\JumpStorage;
use InvalidArgumentException;
use PhpPkg\EasyTpl\SimpleTemplate;
use Toolkit\PFlag\FlagsParser;
use Toolkit\Stdlib\Str;
use Toolkit\Sys\Util\ShellUtil;
use function implode;
use function is_dir;
use function is_string;
use function sprintf;

/**
 * Class JumpController
 */
class JumpController extends Controller
{
    protected static string $name = 'jump';

    protected static string $desc = 'Jump helps you navigate faster by your history.';

    /**
     * @return string[]
     */
    public static function aliases(): array
    {
        return ['goto'];
    }

    /**
     * @return string[][]
     */
    protected static function commandAliases(): array
    {
        return [
            'hint'  => ['match', 'search'],
            'chdir' => ['into'],
            'get'   => ['cd'],
            'list'  => ['ls'],
        ];
    }

    /**
     * list the jump storage data
     *
     * @usage
     *  {binWithCmd}
     *  {binWithCmd} namedPaths
     *  {binWithCmd} histories
     *
     * @arguments
     *  category         The data category key name. allow: namedPaths, histories, prevPath
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function listCommand(FlagsParser $fs, Output $output): void
    {
        $qj = Kite::jumper();
        $output->colored('Datafile: ' . $qj->getDatafile(), 'cyan');
        $output->println(Str::repeat('=', 60));


        $opts = [
            'ucTitleWords' => false,
        ];

        if ($key = $fs->getFirstArg()) {
            $val = $qj->getEngine()->get($key);
            if ($val === null) {
                throw new InvalidArgumentException("invalid data key: $key");
            }

            $output->aList($val, $key, $opts);
        } else {
            $data = $qj->getEngine()->toArray(true);
            $output->mList($data, $opts);
        }
    }

    /**
     * Generate shell script for give shell env name.
     *
     * @usage
     *  {binWithCmd} zsh
     *  {binWithCmd} bash
     *
     * @arguments
     *  shellName   The shell name. if is empty will auto fetch by `$SHELL`. eg: zsh,bash
     *
     * @options
     *  --bind       The shell bind func name. default is `jump`
     *
     * @param FlagsParser $fs
     * @param Output $output
     *
     * @help
     *  quick jump for bash(add to ~/.bashrc):
     *      # shell func is: jump
     *      eval "$(kite jump shell bash)"
     *
     *  quick jump for zsh(add to ~/.zshrc):
     *      # shell func is: jump
     *      eval "$(kite jump shell zsh)"
     *      # set the bind func name is: j
     *      eval "$(kite jump shell zsh --bind j)"
     *
     */
    public function shellCommand(FlagsParser $fs, Output $output): void
    {
        $shell = $fs->getArg('shellName');
        if (!$shell) {
            $shell = ShellUtil::getName(true);
        }

        if (!JumpShell::isSupported($shell)) {
            throw new PromptException("not supported shell name: $shell");
        }

        $qj  = Kite::jumper();
        $tpl = new SimpleTemplate();

        $bindFunc = $fs->getOpt('bind', 'jump');
        $tplVars  = [
            'shell'    => $shell,
            'bindFunc' => $bindFunc,
        ];

        // $tplContents = JumpShell::getShellScript($shell);
        $tplContents = $qj->getShellTplContents($shell);
        $scriptBody  = $tpl->renderString($tplContents, $tplVars);

        // $output->colored("Document for the #$nameString");
        $output->writeRaw($scriptBody);
    }

    /**
     * Match directory paths by given keywords
     *
     * @arguments
     *  keywords   The jump target directory keywords for match.
     *
     * @options
     *  --flag     int;The flag set for match paths.
     *              Allow:
     *              1   Only match name path list
     *              2   Only match history path list
     *              3   match all directory path list(default)
     *  --no-name   bool;Not output name for named paths, useful for bash env.
     *  --limit     bool;Limit the match result rows
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function hintCommand(FlagsParser $fs, Output $output): void
    {
        $qj = Kite::jumper();
        $kw = $fs->getArg('keywords');

        $flag = $fs->getOpt('flag', JumpStorage::MATCH_BOTH);

        $tipsStr = '';
        $results = $qj->matchAll($kw, $flag);

        if ($results) {
            $tipsArr = [];
            $notName = $fs->getOpt('no-name');

            foreach ($results as $name => $path) {
                if (false === $notName && is_string($name)) {
                    $tipsArr[] = sprintf("%s:%s", $name, $path);
                } else {
                    $tipsArr[] = sprintf("%s", $path);
                }
            }

            // addslashes($string);
            Kite::logger()->info('jump hint keywords is: ' . $kw, [
                'results' => $kw ? $tipsArr : 'ALL',
            ]);

            $tipsStr = implode("\n", $tipsArr);
        }

        $output->writeRaw($tipsStr);
    }

    /**
     * Get the real directory path by given name.
     *
     * @arguments
     *  name    The jump target directory name or path.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function getCommand(FlagsParser $fs, Output $output): void
    {
        $qj = Kite::jumper();

        // vdump($input, $_SERVER['argv']);
        $name = $fs->getArg('name');
        $dir  = $qj->match($name);
        // $qj->saveLastMatch($dir);

        Kite::logger()->info("jump get directory is: $dir, name: $name");

        $output->writeRaw($dir, false);
    }

    /**
     * Set the name to real directory path.
     *
     * @arguments
     *  name    string;The name for quick jump;required;
     *  path    string;The target directory path;required;
     *
     * @options
     *  --override       bool;Override exist name.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function setCommand(FlagsParser $fs, Output $output): void
    {
        $qj = Kite::jumper();

        $name = $fs->getArg('name');
        $path = $fs->getArg('path');

        $ok = $qj->addNamed($name, $path, $fs->getOpt('override'));
        if ($ok) {
            $qj->dump();
            $output->success("Set: $name=$path");
        } else {
            $output->liteError('name exists or path is not and dir');
        }
    }

    /**
     * record target directory path, by the jump dir hooks.
     *
     * @arguments
     * targetDir   The into target dir path
     *
     * @options
     *  --quiet       bool;Quiet, not print workdir.
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function chdirCommand(FlagsParser $fs, Output $output): void
    {
        $targetDir = $fs->getArg('targetDir');
        if (!$targetDir) {
            $targetDir = $this->input->getWorkDir();
        }

        $qj = Kite::jumper();
        // update: add new dir path.
        if ($qj->addHistory($targetDir)) {
            Kite::logger()->info('add new dir to history and latest path', [
                'dir' => $targetDir,
            ]);

            $qj->dump();
        }

        if (is_dir($targetDir)) {
            $quiet = $fs->getOpt('quiet');

            if (!$quiet) {
                $output->colored("INTO: $targetDir");
            }
        } else {
            $output->liteError('invalid dir path:' . $targetDir);
        }
    }
}
