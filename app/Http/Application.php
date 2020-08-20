<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Http;

use Inhere\Route\Router;
use function array_merge;
use function file_exists;
use function is_array;
use const BASE_PATH;

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

    public function run()
    {
        $this->router->dispatch();
    }

    /**
     * @param array $config
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
     * @param mixed $default
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
}
