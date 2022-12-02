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
use PhpPkg\Config\ConfigBox;
use PhpPkg\JenkinsClient\MultiJenkins;
use Toolkit\FsUtil\Dir;
use Toolkit\FsUtil\File;
use Toolkit\Stdlib\Obj\ObjectBox;
use Toolkit\Stdlib\Util\PhpDotEnv;
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
 * @method static MultiJenkins jenkins()
 *
 * @see Kite::__callStatic() for quick get object
 */
class Kite
{
    use StaticPathAliasTrait;

    public const VERSION  = '2.2.5';
    public const HOMEPAGE = 'https://github.com/inhere/kite';

    public const PUBLISH_AT  = '2020.05.24';
    public const UPDATED_AT  = '2022.05.18';

    public const MODE_CLI = 'cli';
    public const MODE_WEB = 'web';

    /**
     * @var ObjectBox|null
     */
    private static ?ObjectBox $box = null;

    /**
     * @var ConfigBox|null
     */
    private static ?ConfigBox $cfg = null;

    /**
     * @var CliApplication
     */
    private static CliApplication $cliApp;

    /**
     * @var WebApplication
     */
    private static WebApplication $webApp;

    /*
     * @var ClassLoader
     */
    // public static ClassLoader $loader;

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
     * @return ConfigBox
     */
    public static function config(): ConfigBox
    {
        if (!self::$cfg) {
            self::$cfg = new ConfigBox();
        }

        return self::$cfg;
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
     * @return mixed
     */
    public static function get(string $id): mixed
    {
        return self::box()->get($id);
    }

    /**
     * @return PhpDotEnv
     */
    public static function dotenv(): PhpDotEnv
    {
        return PhpDotEnv::global();
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
     * @param string $path relative path on kite tmp dir.
     *
     * @return string
     */
    public static function getTmpPath(string $path): string
    {
        if (File::isAbsPath($path)) {
            return $path;
        }

        if (IN_PHAR) {
            // see app/boot.php
            return self::resolve('@user-tmp');
        }

        return self::getPath("tmp/$path");
    }

    /**
     * @param string $path relative path on kite root. not need start with '/', eg: 'app/boot.php'
     * @param bool   $rmPharMark Will clear prefix 'phar://', if on phar package.
     *
     * @return string
     */
    public static function getPath(string $path = '', bool $rmPharMark = true): string
    {
        if (!$path) {
            return self::basePath($rmPharMark);
        }

        if (File::isAbsPath($path)) {
            return $path;
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
