<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Http;

use Inhere\Kite\Concern\InitApplicationTrait;
use Inhere\Kite\Kite;
use Inhere\Route\Dispatcher\Dispatcher;
use Inhere\Route\Router;
use PhpPkg\EasyTpl\EasyTemplate;
use Throwable;
use Toolkit\Stdlib\Obj\ObjectBox;
use function array_merge;
use function date_default_timezone_set;

/**
 * Class Application
 *
 * @package Inhere\Kite\Http
 */
class WebApplication
{
    use InitApplicationTrait;

    /**
     * @var EasyTemplate
     */
    private EasyTemplate $renderer;

    /**
     * @var array = [
     *     'debug' => false,
     * ]
     */
    private array $params = [
        'debug' => false,
    ];

    /**
     * The root path for project
     *
     * @var string
     */
    private string $basePath;

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

        $this->loadAppConfig(Kite::MODE_WEB);

        $this->registerServices(Kite::box());

        $this->initAppRun();
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

        Kite::logger()->info('web app init completed');
    }

    /**
     * run
     */
    public function run(): void
    {
        try {
            $dispatcher = Kite::dispatcher();

            // 成功匹配路由
            $dispatcher->on(Dispatcher::ON_FOUND, function ($uri, $cb) {
                Kite::logger()->debug("Matched uri path: $uri, setting callback is: " . is_string($cb) ? $cb : get_class($cb));
            });

            Kite::webRouter()->dispatch($dispatcher);
        } catch (Throwable $e) {
            (new ErrorHandler((bool)$this->params['debug']))->run($e);
        }
    }

    /**
     * @param array $config
     * @param bool  $merge
     */
    public function setParams(array $config, bool $merge = true): void
    {
        if ($merge) {
            $this->params = array_merge($this->params, $config);
        } else {
            $this->params = $config;
        }
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param string $name
     * @param array  $default
     *
     * @return array
     */
    public function getArrayParam(string $name, array $default = []): array
    {
        if (isset($this->config[$name])) {
            return (array)$this->config[$name];
        }

        return $default;
    }

    /**
     * Get config param value
     *
     * @param string $name
     * @param mixed|null $default
     *
     * @return array|mixed
     */
    public function getParam(string $name, mixed $default = null): mixed
    {
        return $this->params[$name] ?? $default;
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return Kite::box()->get('router');
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
     * @return EasyTemplate
     */
    public function getRenderer(): EasyTemplate
    {
        return Kite::box()->get('renderer');
    }
}
