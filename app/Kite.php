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
use Inhere\Kite\Http\Application as WebApplication;
use Inhere\Route\Router;

/**
 * Class Kite
 *
 * @package Inhere\Kite
 */
class Kite
{
    /**
     * @var Application
     */
    private static $cliApp;

    /**
     * @var WebApplication
     */
    private static $webApp;

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
}
