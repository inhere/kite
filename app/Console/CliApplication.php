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
use Inhere\Kite\Console\Listener\BeforeCommandRunListener;
use Inhere\Kite\Console\Listener\BeforeRunListener;
use Inhere\Kite\Lib\Jump\QuickJump;
use Inhere\Kite\Common\Traits\InitApplicationTrait;
use Inhere\Kite\Console\Listener\NotFoundListener;
use Inhere\Kite\Console\Plugin\PluginManager;
use Inhere\Kite\Kite;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
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

    /**
     * @param ObjectBox $box
     */
    protected function registerServices(ObjectBox $box): void
    {
        $this->registerComServices($box);

        // override logger, add processor
        $box->set('logger', function () {
            $config = $this->getParam('logger', []);
            $logger = new Logger($config['name'] ?? 'kite');
            $logger->pushProcessor(new CliLogProcessor());

            $handler = new RotatingFileHandler($config['logfile']);
            $logger->pushHandler($handler);
            return $logger;
        }, true);

        $box->set('plugManager', function () {
            $plugDirs = $this->getParam('pluginDirs', []);
            return new PluginManager($plugDirs);
        });

        $box->set('scriptRunner', function () {
            $config = $this->getParam('scriptRunner', []);
            $scripts = $this->getParam('scripts', []);

            // create object
            $sr = new ScriptRunner($config);
            $sr->setScripts($scripts);
            $scriptDirs = $this->getParam('scriptDirs', []);
            $sr->setScriptDirs($scriptDirs);

            return $sr;
        });

        $box->set('jumper', function () {
            $jumpConf = $this->getParam('jumper', []);
            return QuickJump::new($jumpConf);
        });
    }

    protected function initAppRun(): void
    {
        date_default_timezone_set('PRC');

        $this->on(ConsoleEvent::ON_BEFORE_RUN, new BeforeRunListener());
        $this->on(ConsoleEvent::ON_NOT_FOUND, new NotFoundListener());

        // auto proxy setting
        $autoProxy = $this->getParam('autoProxy', []);
        $this->on(ConsoleEvent::COMMAND_RUN_BEFORE, BeforeCommandRunListener::new($autoProxy));

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
