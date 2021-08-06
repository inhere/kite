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
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Lib\Jump\JumpShell;
use Inhere\Kite\Lib\Template\SimpleTemplate;
use Inhere\Kite\Kite;
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
    protected static $name = 'jump';

    protected static $description = 'Jump helps you navigate faster by learning your habits.';

    public static function aliases(): array
    {
        return ['goto'];
    }

    protected static function commandAliases(): array
    {
        return [
            'hint'  => ['match', 'search'],
            'chdir' => ['into'],
        ];
    }

    protected function configure(): void
    {
        parent::configure();

        // simple binding arguments
        switch ($this->getAction()) {
            case 'shell':
                $this->input->bindArgument('shellName', 0);
                break;
            case 'hint':
                $this->input->bindArgument('keywords', 0);
                break;
            case 'get':
                $this->input->bindArgument('name', 0);
                break;
            case 'set':
                $this->input->bindArgument('name', 0);
                $this->input->bindArgument('path', 1);
                break;
        }
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
     *  key         The data key name.
     *
     * @param Input  $input
     * @param Output $output
     */
    public function listCommand(Input $input, Output $output): void
    {
        $qj = Kite::jumper();
        $output->colored('Datafile: ' . $qj->getDatafile(), 'cyan');

        $key  = $input->getFirstArg();
        $data = $qj->getEngine()->toArray(true);

        if ($key && isset($data[$key])) {
            $output->aList($data[$key], $key);
        } else {
            $output->mList($data);
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
     * @param Input  $input
     * @param Output $output
     *
     * @help
     *  auto jump for bash(add to ~/.bashrc):
     *      # shell func is: jump
     *      eval "$(kite jump shell bash)"
     *
     *  auto jump for zsh(add to ~/.zshrc):
     *      # shell func is: jump
     *      eval "$(kite jump shell zsh)"
     *      # set the bind func name is: j
     *      eval "$(kite jump shell zsh --bind j)"
     *
     */
    public function shellCommand(Input $input, Output $output): void
    {
        $shell = $input->getStringArg('shellName');
        if (!$shell) {
            $shell = ShellUtil::getName(true);
        }

        if (!JumpShell::isSupported($shell)) {
            throw new PromptException("not supported shell name: $shell");
        }

        $tpl = new SimpleTemplate();

        $bindFunc   = $input->getStringOpt('bind', 'jump');
        $scriptBody = $tpl->renderString(JumpShell::getShellScript($shell), [
            'shell'    => $shell,
            'bindFunc' => $bindFunc,
        ]);

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
     *  --flag         The flag set for match
     *          both      match all directory list
     *          mark      Only match marks directory list
     *          history   Only match history directory list
     *  --limit         Limit the match result rows
     *
     * @param Input  $input
     * @param Output $output
     */
    public function hintCommand(Input $input, Output $output): void
    {
        $qj = Kite::jumper();

        $name = $input->getStringArg('keywords');
        // $flag = $input->getStringOpt('flag', 'both');
        Kite::logger()->info('jump hint keywords is: ' . $name);

        $tipsStr = '';
        $results = $qj->matchAll($name);

        if ($results) {
            // $tips = sprintf("'%s'", implode( "' '", $dirs));
            // $tips = implode('', $dirs);
            # commands for use `_describe`
            # commands+=('test1:/path/to/dir1' 'test2:/path/to/dir2')
            // $tips = "'" . implode("'\n'", $dirs) . "'";
            $tipsArr = [];
            foreach ($results as $name => $path) {
                if (is_string($name)) {
                    $tipsArr[] = sprintf("%s:%s", $name, $path);
                } else {
                    $tipsArr[] = sprintf("%s", $path);
                }
            }

            $tipsStr = implode("\n", $tipsArr);
        }

        $output->writeRaw($tipsStr);
    }

    /**
     * Get the real directory path by given name.
     *
     * @arguments
     *  name   The jump target directory name or path.
     *
     * @param Input  $input
     * @param Output $output
     */
    public function getCommand(Input $input, Output $output): void
    {
        $qj = Kite::jumper();

        $name = $input->getStringArg('name');
        $dir  = $qj->match($name);

        Kite::logger()->info("jump get dir is: $dir, name: $name");

        $output->writeRaw($dir, false);
    }

    /**
     * Set the name to real directory path.
     *
     * @arguments
     *  name   The name for quick jump.
     *  path   The target directory path.
     *
     * @options
     *  --override       Override exist name.
     *
     * @param Input  $input
     * @param Output $output
     */
    public function setCommand(Input $input, Output $output): void
    {
        $qj = Kite::jumper();

        $name = (string)$input->getRequiredArg('name');
        $path = (string)$input->getRequiredArg('path');

        $ok = $qj->addNamed($name, $path, $input->getBoolOpt('override'));
        if ($ok) {
            $qj->dump();
            $output->success("Set: $name=$path");
        } else {
            $output->liteError('name exists or path is not and dir');
        }
    }

    /**
     * by the jump dir hook, record target directory path.
     *
     * @param Input  $input
     * @param Output $output
     */
    public function chdirCommand(Input $input, Output $output): void
    {
        $targetDir = $input->getStringArg(0);
        if (!$targetDir) {
            $targetDir = $input->getWorkDir();
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
            $output->colored("INTO: $targetDir");
        } else {
            $output->liteError('invalid dir path:' . $targetDir);
        }
    }
}
