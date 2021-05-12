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
use Inhere\Kite\Common\InitApplicationTrait;
use Inhere\Kite\Console\Listener\NotFoundListener;
use Inhere\Kite\Console\Plugin\PluginManager;
use Inhere\Kite\Kite;
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
    use InitApplicationTrait;

    /**
     * @var PluginManager
     */
    private $plugManager;

    protected function prepareRun(): void
    {
        parent::prepareRun();

        date_default_timezone_set('PRC');
    }

    protected function init(): void
    {
        parent::init();

        Kite::setCliApp($this);

        $workDir = $this->getInput()->getPwd();

        $this->loadAppConfig($workDir);

        $this->initAppRun();
    }

    protected function initAppRun(): void
    {
        $plugDirs = $this->getParam('pluginDirs', []);

        $this->plugManager = new PluginManager($plugDirs);

        $this->on(ConsoleEvent::ON_NOT_FOUND, new NotFoundListener());
    }

    /**
     * @return PluginManager
     */
    public function getPlugManager(): PluginManager
    {
        return $this->plugManager;
    }
}
