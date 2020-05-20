<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-02-27
 * Time: 18:58
 */

namespace Inhere\PTool\Console\Command;

use Inhere\Console\Command;
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
    protected $libsDir;

    /**
     * @var string
     */
    protected $repoDir;

    public function __construct()
    {
        $this->baseDir = BASE_PATH;
        $this->repoDir = App::$i->getPwd();
        $this->libsDir = $this->repoDir . '/src/';
    }

    /**
     * do execute
     * @param  \Inhere\Console\IO\Input $input
     * @param  \Inhere\Console\IO\Output $output
     * @return int
     */
    protected function execute($input, $output)
    {
        Color::println('Update to latest:');

        $cmd = "cd {$this->baseDir} && git checkout . && git pull";
        $ret = self::exec($cmd);

        echo $ret['output'];

        Color::println('Add execute perm:');

        $binName = $app->getScriptName();
        self::exec("cd {$this->baseDir} && chmod a+x bin/$binName");

        Color::println('Complete');
    }
}
