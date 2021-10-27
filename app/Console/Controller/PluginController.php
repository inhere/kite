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
use Inhere\Console\IO\Output;
use Inhere\Kite\Kite;
use InvalidArgumentException;
use Toolkit\Cli\Util\LineParser;
use Toolkit\PFlag\FlagsParser;
use function array_keys;

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

    protected static function commandAliases(): array
    {
        return [
            'info' => ['show'],
        ];
    }

    /**
     * list all plugins dir and file information
     *
     * @options
     *  --only-files    bool;Only list all plugin names
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function listCommand(FlagsParser $fs, Output $output): void
    {
        $kpm = Kite::plugManager();
        $opts = ['ucFirst' => false];

        if (!$fs->getOpt('only-files')) {
            $dirs = $kpm->getPluginDirs();
            $output->aList($dirs, 'plugin dirs', $opts);
        }

        $files = $kpm->loadPluginFiles()->getPluginFiles();
        $output->aList($files, 'Plugin Files', $opts);
    }

    /**
     * display information for an plugin
     *
     * @arguments
     *  name    string;The plugin name for display;required
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function infoCommand(FlagsParser $fs, Output $output): void
    {
        $kpm  = Kite::plugManager();
        $name = $fs->getArg('name');

        if (!$plg= $kpm->getPlugin($name)) {
            $output->error("the plugin '$name' is not exists");
            return;
        }

        // $opts = ['ucFirst' => false];
        // $output->aList($plg->getInfo(), 'Plugin Info', $opts);
        $kpm->showInfo($plg);
    }

    /**
     * run an plugin by input name
     *
     * @arguments
     *  name    The plugin name for run
     *
     * @options
     *  -i, -s, --select    bool;select a plugin in plugin list for run
     *
     * @param FlagsParser $fs
     * @param Output $output
     */
    public function runCommand(FlagsParser $fs, Output $output): void
    {
        $kpm = Kite::plugManager();

        if ($fs->getOpt('select')) {
            $files = $kpm->loadPluginFiles()->getPluginFiles();

            $list = array_keys($files);
            $name = $output->select('select an plugin for run', $list, null, true, [
                'returnVal' => true,
            ]);

            $line = $output->ask("please provide args for the plugin '$name'", '-h');
            $args = LineParser::parseIt($line);

            $output->title("Run the plugin $name", [
                'ucWords' => false,
            ]);
        } else {
            $name = $fs->getArg('name');
            if (!$name) {
                throw new InvalidArgumentException('must provide plugin name for run');
            }

            $args = $fs->getRemainArgs();
        }

        $kpm->run($name, $this->app, $args);
    }
}
