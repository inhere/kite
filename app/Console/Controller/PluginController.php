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
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Inhere\Kite\Kite;

/**
 * Class PluginController
 */
class PluginController extends Controller
{
    protected static $name = 'plugin';

    protected static $description = 'kite plugins manage tools';

    /**
     * @return string[]
     */
    public static function aliases(): array
    {
        return ['plugins', 'plug'];
    }

    /**
     * run an plugin by input name
     *
     * @arguments
     *  name    The plugin name for run
     *
     * @param Input  $input
     * @param Output $output
     */
    public function runCommand(Input $input, Output $output): void
    {
        $input->bindArgument('name', 0);
        $name = $input->getRequiredArg('name');

        $kpm = Kite::plugManager();
        $kpm->run($name, $this->app);

        $output->success('completed');
    }

    /**
     * list all plugins dir and file information
     *
     * @options
     *  --only-files    Only list all plugin names
     *
     * @param Input  $input
     * @param Output $output
     */
    public function listCommand(Input $input, Output $output): void
    {
        $kpm = Kite::plugManager();
        $opts = ['ucFirst' => false];

        if (!$input->getBoolOpt('only-files')) {
            $dirs = $kpm->getPluginDirs();
            $output->aList($dirs, 'plugin dirs', $opts);
        }

        $files = $kpm->loadPluginFiles()->getPluginFiles();
        $output->aList($files, 'Plugin Files', $opts);
    }
}
