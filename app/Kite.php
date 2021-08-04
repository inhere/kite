<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite;

use Inhere\Kite\Console\Application;
use Inhere\Kite\Console\Plugin\PluginManager;
use Inhere\Kite\Http\Application as WebApplication;
use Inhere\Route\Router;
use Toolkit\Stdlib\Obj\ObjectBox;

/**
 * Class Kite
 *
 * @package Inhere\Kite
 */
class Kite
{
    public const VERSION = '1.0.11';

    /**
     * @var Application
     */
    private static $cliApp;

    /**
     * @var WebApplication
     */
    private static $webApp;

    /**
     * @return ObjectBox
     */
    public static function objs(): ObjectBox
    {
        return ObjectBox::global();
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
     * @return Application
     */
    public static function cliApp(): Application
    {
        return self::$cliApp;
    }

    /**
     * @return Router
     */
    public static function webRouter(): Router
    {
        return self::$webApp->getRouter();
    }

    /**
     * @return PluginManager
     */
    public static function plugManager(): PluginManager
    {
        return self::$cliApp->getPlugManager();
    }

    /**
     * @param Application $cliApp
     */
    public static function setCliApp(Application $cliApp): void
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
