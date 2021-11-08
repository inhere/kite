<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite;

use BadMethodCallException;
use Inhere\Kite\Component\ScriptRunner;
use Inhere\Kite\Concern\StaticPathAliasTrait;
use Inhere\Kite\Console\CliApplication;
use Inhere\Kite\Console\Component\AutoSetProxyEnv;
use Inhere\Kite\Console\Plugin\PluginManager;
use Inhere\Kite\Http\WebApplication;
use Inhere\Kite\Lib\Jump\QuickJump;
use Inhere\Route\Dispatcher\Dispatcher;
use Inhere\Route\Router;
use Monolog\Logger;
use Toolkit\FsUtil\Dir;
use Toolkit\Stdlib\Obj\ObjectBox;
use Toolkit\Stdlib\OS;
use const BASE_PATH;
use const IN_PHAR;

/**
 * Class Kite
 *
 * @package Inhere\Kite
 *
 * @method static QuickJump jumper()
 * @method static AutoSetProxyEnv autoProxy()
 * @method static Router webRouter()
 * @method static Dispatcher dispatcher()
 * @method static ScriptRunner scriptRunner()
 *
 * @see     Kite::__callStatic() for quick get object
 */
class Kite
{
    use StaticPathAliasTrait;

    public const VERSION  = '2.0.0';
    public const HOMEPAGE = 'https://github.com/inhere/kite';

    public const MODE_CLI = 'cli';
    public const MODE_WEB = 'web';

    /**
     * @var ObjectBox
     */
    private static $box;

    /**
     * @var CliApplication
     */
    private static $cliApp;

    /**
     * @var WebApplication
     */
    private static $webApp;

    /**
     * @return ObjectBox
     */
    public static function box(): ObjectBox
    {
        if (!self::$box) {
            self::$box = new ObjectBox();
        }

        return self::$box;
    }

    /**
     * @param string $method
     * @param array  $args
     *
     * @return mixed|object
     */
    public static function __callStatic(string $method, array $args = [])
    {
        if (self::box()->has($method)) {
            return self::box()->get($method);
        }

        throw new BadMethodCallException('call not exist method: ' . $method);
    }

    /**
     * @param string $id
     *
     * @return mixed|object
     */
    public static function get(string $id)
    {
        return self::box()->get($id);
    }

    /**
     * @return PluginManager
     */
    public static function plugManager(): PluginManager
    {
        return self::box()->get('plugManager');
    }

    /**
     * @return Logger
     */
    public static function logger(): Logger
    {
        return self::box()->get('logger');
    }

    /**
     * @param bool $rmPharMark Will clear prefix 'phar://', if on phar package.
     *
     * @return string
     */
    public static function basePath(bool $rmPharMark = true): string
    {
        return $rmPharMark ? Dir::clearPharPath(BASE_PATH) : BASE_PATH;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public static function getTmpPath(string $path): string
    {
        if (IN_PHAR) {
            // see app/boot.php
            return self::resolve('@user-tmp');
        }

        return self::getPath("tmp/$path");
    }

    /**
     * @param string $path
     * @param bool   $rmPharMark Will clear prefix 'phar://', if on phar package.
     *
     * @return string
     */
    public static function getPath(string $path = '', bool $rmPharMark = true): string
    {
        if (!$path) {
            return self::basePath($rmPharMark);
        }

        return self::basePath($rmPharMark) . '/' . $path;
    }

    /**
     * @return WebApplication
     */
    public static function app(): WebApplication
    {
        return self::$webApp;
    }

    /**
     * @return WebApplication
     */
    public static function webApp(): WebApplication
    {
        return self::$webApp;
    }

    /**
     * @return CliApplication
     */
    public static function cliApp(): CliApplication
    {
        return self::$cliApp;
    }

    /**
     * @param CliApplication $cliApp
     */
    public static function setCliApp(CliApplication $cliApp): void
    {
        self::$cliApp = $cliApp;
    }

    /**
     * @param WebApplication $webApp
     */
    public static function setWebApp(WebApplication $webApp): void
    {
        self::$webApp = $webApp;
    }
}
