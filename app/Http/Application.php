<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Http;

use Inhere\Kite\Common\Template\HtmlTemplate;
use Inhere\Kite\Common\Traits\InitApplicationTrait;
use Inhere\Kite\Kite;
use Inhere\Route\Dispatcher\Dispatcher;
use Inhere\Route\Router;
use Throwable;
use Toolkit\Stdlib\Obj\ObjectBox;
use function array_merge;
use function date_default_timezone_set;

/**
 * Class Application
 *
 * @package Inhere\Kite\Http
 */
class Application
{
    use InitApplicationTrait;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var HtmlTemplate
     */
    private $renderer;

    /**
     * @var array
     */
    private $config = [];

    /**
     * The root path for project
     *
     * @var string
     */
    private $basePath;

    /**
     * Class constructor.
     *
     * @param string $basePath
     */
    public function __construct(string $basePath)
    {
        Kite::setWebApp($this);

        $this->basePath = $basePath;
        $this->prepare();
    }

    protected function prepare(): void
    {
        $this->loadEnvSettings();

        $this->loadAppConfig();

        $this->registerServices(Kite::objs());

        $this->initAppRun();
    }

    /**
     * @param ObjectBox $box
     */
    protected function registerServices(ObjectBox $box): void
    {
        $this->registerComServices($box);

        $box->set('router', function () {
            return new Router();
        });
        $box->set('renderer', function () {
            $config = $this->getParam('renderer', []);

            return new HtmlTemplate($config);
        });
    }

    protected function initAppRun(): void
    {
        date_default_timezone_set('PRC');

        Kite::logger()->info('web app init completed');
    }

    /**
     * run
     */
    public function run(): void
    {
        try {
            $dispatcher = new Dispatcher([
                'actionSuffix' => '',
            ]);

            $this->router->dispatch($dispatcher);
        } catch (Throwable $e) {
            (new ErrorHandler())->run($e);
        }
    }

    /**
     * @param array $config
     * @param bool  $merge
     */
    public function setConfig(array $config, bool $merge = true): void
    {
        if ($merge) {
            $this->config = array_merge($this->config, $config);
        } else {
            $this->config = $config;
        }
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get config param value
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return array|mixed
     */
    public function getParam(string $name, $default = null)
    {
        return $this->config[$name] ?? $default;
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return Kite::objs()->get('router');
    }

    /**
     * @param string $subPath
     *
     * @return string
     * @example eg: $app->getPath('runtime')
     */
    public function getPath(string $subPath): string
    {
        if ($subPath) {
            return $this->basePath . '/' . $subPath;
        }

        return $this->basePath;
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @return HtmlTemplate
     */
    public function getRenderer(): HtmlTemplate
    {
        return Kite::objs()->get('renderer');
    }
}
