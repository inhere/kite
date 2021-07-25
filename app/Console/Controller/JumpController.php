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
use Inhere\Kite\Common\Jump\JumpShell;
use Inhere\Kite\Common\Jump\QuickJumpDir;
use Inhere\Kite\Common\MapObject;
use Inhere\Kite\Common\Template\SimpleTemplate;
use Toolkit\Sys\Util\ShellUtil;
use function implode;
use function vdump;

/**
 * Class JumpController
 */
class JumpController extends Controller
{
    protected static $name = 'jump';

    protected static $description = 'Jump helps you navigate faster by learning your habits.';

    /**
     * @var MapObject
     */
    private $settings;

    public static function aliases(): array
    {
        return ['goto'];
    }

    protected function beforeRun(): void
    {
        if ($this->app && !$this->settings) {
            $this->settings = MapObject::new($this->app->getParam('jump', []));
        }
    }

    protected function configure(): void
    {
        parent::configure();

        // simple binding arguments
        switch ($this->getAction()) {
            case 'shell':
                $this->input->bindArgument('shellName', 0);
                break;
            case 'cd':
                $this->input->bindArgument('name', 0);
                break;
        }
    }

    /**
     * manage the jump config or storage data
     *
     * @usage
     *  {binWithCmd} -l
     *
     * @options
     *  -l, --list      list all config
     *      --get       Get an config
     *      --set       Set the named path to data.
     *
     * @param Input  $input
     * @param Output $output
     */
    public function configCommand(Input $input, Output $output): void
    {
        $jd = new QuickJumpDir($this->settings->toArray());
        $jd->run();
        vdump($jd->getDatafile());
        if ($input->getSameBoolOpt('l,list')) {
            $data = $jd->getEngine()->toArray(true);
            $output->mList($data);
            return;
        }

        $output->writeln("TODO");
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
     * Get the real directory path by given name.
     *
     * @arguments
     *  name   The jump target directory name or path.
     *
     * @param Input  $input
     * @param Output $output
     */
    public function cdCommand(Input $input, Output $output): void
    {
        $jd = new QuickJumpDir($this->settings->toArray());
        $jd->run();

        $name = $input->getStringArg('name');
        $dir  = $jd->match($name);

        $output->writeRaw($dir, false);
    }

    /**
     * dump current work directory path.
     *
     * @param Input  $input
     * @param Output $output
     */
    public function chdirCommand(Input $input, Output $output): void
    {
        $curDir = $input->getWorkDir();
        $output->writeln("INTO: $curDir");
    }

    /**
     * Match directory paths by given keywords
     *
     * @arguments
     *  keywords   The jump target directory keywords for match.
     *
     * @options
     *  --limit       Limit the match result rows.
     *
     * @param Input  $input
     * @param Output $output
     */
    public function hintCommand(Input $input, Output $output): void
    {
        $jd = new QuickJumpDir($this->settings->toArray());
        $jd->run();

        $name = $input->getStringArg('name');
        // vdump($name, strlen($name));
        $dirs = $jd->matchAll($name);

        $tips = '';
        if ($dirs) {
            // $tips = sprintf("'%s'", implode( "' '", $dirs));
            $tips = implode('', $dirs);
        }

        $output->writeRaw($tips);
    }
}
