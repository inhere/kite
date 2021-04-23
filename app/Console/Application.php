<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console;

use Inhere\Console\ConsoleEvent;
use Inhere\Kite\Common\CmdRunner;
use Inhere\Kite\Kite;
use Inhere\Kite\Plugin\AbstractPlugin;
use Toolkit\Stdlib\Arr\ArrayHelper;
use function file_exists;
use const BASE_PATH;

/**
 * Class Application
 *
 * @package Inhere\Kite\Console
 */
class Application extends \Inhere\Console\Application
{
    /**
     * loaded plugin objects
     *
     * @var AbstractPlugin[]
     */
    private $plugins = [];

    /**
     * @var array
     */
    private $pluginDirs = [];

    /**
     * @var array
     */
    private $pluginFiles = [];

    /**
     * @var array
     */
    private $pluginClasses = [];

    protected function prepareRun(): void
    {
        parent::prepareRun();

        date_default_timezone_set('PRC');
    }

    protected function init(): void
    {
        parent::init();

        Kite::setCliApp($this);

        $this->loadAppConfig();

        $this->initAppEnv();

        $this->on(ConsoleEvent::ON_NOT_FOUND, $this->onNotFound());
    }

    private function loadAppConfig(): void
    {
        $baseFile = BASE_PATH . '/config/config.php';
        $loaded   = [$baseFile];

        // 基础配置
        /** @noinspection PhpIncludeInspection */
        $config = require $baseFile;

        // 自定义全局配置
        $globFile = BASE_PATH . '/.kite.inc';
        if (file_exists($globFile)) {
            $loaded[] = $globFile;
            /** @noinspection PhpIncludeInspection */
            $userConfig = require $globFile;
            // merge to config
            $config = ArrayHelper::quickMerge($userConfig, $config);
        }

        // 当前项目配置
        $workDir = $this->getInput()->getPwd();
        $proFile = $workDir . '/.kite.inc';
        if ($proFile !== $globFile && file_exists($proFile)) {
            $loaded[] = $proFile;
            /** @noinspection PhpIncludeInspection */
            $proConfig = require $proFile;
            // merge to config
            $config = ArrayHelper::quickMerge($proConfig, $config);
        }

        $config['__loaded_file'] = $loaded;
        $this->setConfig($config);
    }

    protected function initAppEnv(): void
    {
        $this->pluginDirs = $this->getParam('pluginDirs', []);
    }

    protected function onNotFound(): callable
    {
        return static function (string $cmd, Application $app) {
            $aliases = $app->getParam('aliases', []);

            // - is an command alias.
            if ($aliases && isset($aliases[$cmd])) {
                $realCmd = $aliases[$cmd];

                $app->notice("input command is alias name, will redirect to the real command '$realCmd'");
                $app->dispatch($realCmd);
                return true;
            }

            // check custom scripts
            $scripts = $app->getParam('scripts', []);
            if (!$scripts || !isset($scripts[$cmd])) {
                // - run plugin
                if ($app->isPlugin($cmd)) {

                }

                // - call system command.
                if ($cmd[0] === '\\') {
                    $cmd = substr($cmd, 1);
                }

                $cmdLine = $app->getInput()->getFullScript();
                $app->notice("input command is not found, will call system command: $cmdLine");

                // call system command
                CmdRunner::new($cmdLine)->do(true);
                return true;
            }

            // - run custom scripts.
            /** @see \Inhere\Kite\Console\Command\RunCommand::execute() */
            $app->note("command not found, redirect to run script: $cmd");

            $args = $app->getInput()->getArgs();
            $args = array_merge([$cmd], $args);

            $app->getInput()->setArgs($args, true);
            $app->dispatch('run');

            return true;
        };
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isPlugin(string $name): bool
    {
        if (\strpos($name, ' ') !== false) {
            return false;
        }

        foreach ($this->pluginDirs as $dir) {
            $filename = $dir . '/' . $name . '.php';
            if (\is_file($filename)) {

            }
        }

        return false;
    }

    /**
     * @param string $name
     */
    public function runPlugin(string $name): bool
    {
        return true;
    }
}
