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
use Inhere\Kite\Common\Jump\QuickJump;
use Inhere\Kite\Common\Traits\InitApplicationTrait;
use Inhere\Kite\Console\Listener\NotFoundListener;
use Inhere\Kite\Console\Plugin\PluginManager;
use Inhere\Kite\Kite;
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

    protected function prepareRun(): void
    {
        parent::prepareRun();
    }

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

        $box->set('plugManager', function () {
            $plugDirs = $this->getParam('pluginDirs', []);
            return new PluginManager($plugDirs);
        });

        $box->set('jumper', function () {
            $jumpConf = $this->getParam('jump', []);
            return QuickJump::new($jumpConf);
        });
    }

    protected function initAppRun(): void
    {
        date_default_timezone_set('PRC');

        $this->on(ConsoleEvent::ON_NOT_FOUND, new NotFoundListener());

        Kite::logger()->info('console app init completed');
    }

    /**
     * @param array $config
     */
    public function setParams(array $config): void
    {
        $this->setConfig($config);
    }
}
