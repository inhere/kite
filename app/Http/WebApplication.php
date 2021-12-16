<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Http;

use PhpPkg\EasyTpl\HtmlTemplate;
use Inhere\Kite\Concern\InitApplicationTrait;
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
class WebApplication
{
    use InitApplicationTrait;

    /**
     * @var HtmlTemplate
     */
    private HtmlTemplate $renderer;

    /**
     * @var array
     */
    private array $params = [];

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

        $box->set('webRouter', function () {
            return new Router();
        });
        $box->set('renderer', function () {
            $config = $this->getArrayParam('renderer');
            return new HtmlTemplate($config);
        });
        $box->set('dispatcher', [
            'class'        => Dispatcher::class,
            // prop settings
            'actionSuffix' => '',
        ]);
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

            Kite::webRouter()->dispatch($dispatcher);
        } catch (Throwable $e) {
            (new ErrorHandler())->run($e);
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
     * @return HtmlTemplate
     */
    public function getRenderer(): HtmlTemplate
    {
        return Kite::box()->get('renderer');
    }
}
