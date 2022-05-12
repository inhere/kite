<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console;

use Inhere\Console\Application;
use Inhere\Console\ConsoleEvent;
use Inhere\Console\GlobalOption;
use Inhere\Kite\Common\Log\CliLogProcessor;
use Inhere\Kite\Component\ScriptRunner;
use Inhere\Kite\Concern\InitApplicationTrait;
use Inhere\Kite\Console\Component\AutoSetProxyEnv;
use Inhere\Kite\Console\Listener\BeforeCommandRunListener;
use Inhere\Kite\Console\Listener\BeforeRunListener;
use Inhere\Kite\Console\Listener\NotFoundListener;
use Inhere\Kite\Console\Manager\ToolManager;
use Inhere\Kite\Console\Plugin\PluginManager;
use Inhere\Kite\Kite;
use Inhere\Kite\Lib\Jump\QuickJump;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Throwable;
use Toolkit\Stdlib\Obj\ObjectBox;
use function date_default_timezone_set;

/**
 * Class Application
 *
 * @package Inhere\Kite\Console
 */
class CliApplication extends Application
{
    use InitApplicationTrait;

    /**
     * @var PluginManager
     */
    private PluginManager $plugManager;

    // protected function prepareRun(): void
    // {
    //     parent::prepareRun();
    // }

    protected function init(): void
    {
        parent::init();

        Kite::setCliApp($this);

        $this->loadEnvSettings();

        $workDir = $this->getInput()->getPwd();
        $this->loadAppConfig(Kite::MODE_CLI, $workDir);

        $this->registerServices(Kite::box());

        $this->initAppRun();
    }

    public function handleException(Throwable $e): void
    {
        Kite::logger()->error((string)$e);

        parent::handleException($e);
    }

    /**
     * @param ObjectBox $box
     */
    protected function registerServices(ObjectBox $box): void
    {
        $this->registerComServices($box);

        // override logger, add processor
        $box->set('logger', function () {
            $config = $this->config()->getArray('logger');
            $logger = new Logger($config['name'] ?? 'kite');
            $logger->pushProcessor(new CliLogProcessor());

            $handler = new RotatingFileHandler($config['logfile']);
            $logger->pushHandler($handler);
            return $logger;
        }, true);

        $box->set('plugManager', function () {
            $config = $this->config()->getArray('pluginManager');
            return new PluginManager($config);
        });

        $box->set('toolManager', function () {
            $config = $this->config()->getArray('toolManager');
            return new ToolManager($config);
        });

        $box->set('scriptRunner', function () {
            $config = $this->config()->getArray('scriptRunner');
            $scripts = $this->config()->getArray('scripts');

            // create object
            $sr = new ScriptRunner($config);
            $sr->setScripts($scripts);
            $sr->scriptDirs = $this->config()->getArray('scriptDirs');

            return $sr;
        });

        $box->set('jumper', function () {
            $jumpConf = $this->config()->getArray('jumper');
            return QuickJump::new($jumpConf);
        });

        // auto proxy setting
        $box->set('autoProxy', function () {
            $autoProxy = $this->config()->getArray('autoProxy');
            return AutoSetProxyEnv::new($autoProxy);
        });

        // $box->set('envLoader', function () {
        //     $jumpConf = $this->config()->getArray('osEnv');
        //     return QuickJump::new($jumpConf);
        // });
    }

    protected function initAppRun(): void
    {
        date_default_timezone_set('PRC');

        $this->on(ConsoleEvent::ON_BEFORE_RUN, new BeforeRunListener());
        $this->on(ConsoleEvent::ON_NOT_FOUND, new NotFoundListener());

        // auto proxy setting
        $this->on(ConsoleEvent::COMMAND_RUN_BEFORE, new BeforeCommandRunListener);

        // add global option
        GlobalOption::setOption('workdir', [
            'desc' => 'set the global workdir for all commands'
        ]);

        Kite::logger()->info('console app init completed');
    }

    // protected function buildVersionInfo(): array
    // {
    //     $info = parent::buildVersionInfo();
    //
    //     $info[] = '---------';
    //     $info['Homepage'] = Kite::HOMEPAGE;
    //
    //     return $info;
    // }

    /**
     * @param array $config
     */
    public function setParams(array $config): void
    {
        $this->setConfig($config);
    }
}
