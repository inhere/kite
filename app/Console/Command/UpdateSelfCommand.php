<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console\Command;

use Inhere\Console\Command;
use Inhere\Console\Console;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Helper\SysCmd;
use Toolkit\Cli\Color;

/**
 * Class UpdateSelfCommand
 */
class UpdateSelfCommand extends Command
{
    protected static $name = 'updateself';

    protected static $description = 'update self to latest by git pull';

    /**
     * @var string
     */
    protected $baseDir;

    /**
     * @var string
     */
    protected $repoDir;

    public static function aliases(): array
    {
        return ['upself', 'update-self'];
    }

    protected function init(): void
    {
        $this->baseDir = BASE_PATH;
        $this->repoDir = Console::app()->getInput()->getPwd();
    }

    /**
     * do execute
     * @param  Input $input
     * @param  Output $output
     */
    protected function execute($input, $output)
    {
        Color::println('Update to latest:');

        $cmd = "cd {$this->baseDir} && git checkout . && git pull";
        $ret = SysCmd::exec($cmd);

        echo $ret['output'];

        Color::println('Add execute perm:');

        $binName = $input->getScriptName();
        SysCmd::exec("cd {$this->baseDir} && chmod a+x bin/$binName");

        Color::println('Complete');
    }
}
