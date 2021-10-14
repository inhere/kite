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
use Inhere\Kite\Common\Log\CliLogProcessor;
use Inhere\Kite\Component\ScriptRunner;
use Inhere\Kite\Console\Component\AutoSetProxyEnv;
use Inhere\Kite\Console\Listener\BeforeCommandRunListener;
use Inhere\Kite\Console\Listener\BeforeRunListener;
use Inhere\Kite\Lib\Jump\QuickJump;
use Inhere\Kite\Common\Traits\InitApplicationTrait;
use Inhere\Kite\Console\Listener\NotFoundListener;
use Inhere\Kite\Console\Plugin\PluginManager;
use Inhere\Kite\Kite;
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
    private $plugManager;

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
            $config = $this->getArrayParam('logger');
            $logger = new Logger($config['name'] ?? 'kite');
            $logger->pushProcessor(new CliLogProcessor());

            $handler = new RotatingFileHandler($config['logfile']);
            $logger->pushHandler($handler);
            return $logger;
        }, true);

        $box->set('plugManager', function () {
            $plugDirs = $this->getArrayParam('pluginDirs');
            return new PluginManager($plugDirs);
        });

        $box->set('scriptRunner', function () {
            $config = $this->getArrayParam('scriptRunner');
            $scripts = $this->getArrayParam('scripts');

            // create object
            $sr = new ScriptRunner($config);
            $sr->setScripts($scripts);
            $scriptDirs = $this->getArrayParam('scriptDirs');
            $sr->setScriptDirs($scriptDirs);

            return $sr;
        });

        $box->set('jumper', function () {
            $jumpConf = $this->getArrayParam('jumper');
            return QuickJump::new($jumpConf);
        });

        // auto proxy setting
        $box->set('autoProxy', function () {
            $autoProxy = $this->getArrayParam('autoProxy');
            return AutoSetProxyEnv::new($autoProxy);
        });

        // $box->set('envLoader', function () {
        //     $jumpConf = $this->getArrayParam('osEnv');
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
