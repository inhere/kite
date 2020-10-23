<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Http;

use Inhere\Kite\Kite;
use Inhere\Route\Router;
use Throwable;
use function array_merge;

/**
 * Class Application
 *
 * @package Inhere\Kite\Http
 */
class Application
{
    /**
     * @var Router
     */
    private $router;

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
        $this->router = new Router();
    }

    /**
     * run
     */
    public function run(): void
    {
        try {
            $this->router->dispatch();
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
        return $this->router;
    }

    /**
     * @param Router $router
     */
    public function setRouter(Router $router): void
    {
        $this->router = $router;
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
}
