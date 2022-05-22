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
use Inhere\Kite\Concern\InitApplicationTrait;
use Inhere\Kite\Console\Listener\BeforeCommandRunListener;
use Inhere\Kite\Console\Listener\BeforeRunListener;
use Inhere\Kite\Console\Listener\NotFoundListener;
use Inhere\Kite\Console\Plugin\PluginManager;
use Inhere\Kite\Kite;
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

        // register services
        require 'services.php';
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
