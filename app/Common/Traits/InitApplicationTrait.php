<?php declare(strict_types=1);

namespace Inhere\Kite\Common\Traits;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Toolkit\Stdlib\Arr\ArrayHelper;
use Toolkit\Stdlib\Obj\ObjectBox;
use Toolkit\Stdlib\OS;
use Toolkit\Stdlib\Util\PhpDotEnv;
use function file_exists;
use const BASE_PATH;

/**
 * Trait InitApplicationTrait
 *
 * @package Inhere\Kite\Common
 */
trait InitApplicationTrait
{
    protected function loadEnvSettings(): void
    {
        // get user homedir
        $homeDir = OS::getUserHomeDir();
        PhpDotEnv::load($homeDir . '/.config/.kite.env');
    }

    /**
     * @param string $runMode
     * @param string $workDir
     */
    protected function loadAppConfig(string $runMode, string $workDir = ''): void
    {
        $baseFile = BASE_PATH . '/config/config.php';
        $loaded   = [$baseFile];

        // 基础配置
        /** @noinspection PhpIncludeInspection */
        $config = require $baseFile;

        // eg: config.web.php
        $modeFile = BASE_PATH . "/config/config.$runMode.php";
        if (file_exists($modeFile)) {
            $loaded[] = $modeFile;
            /** @noinspection PhpIncludeInspection */
            $modeConfig = require $modeFile;
            // merge to config
            $config = ArrayHelper::quickMerge($modeConfig, $config);
        }

        // 自定义全局配置
        $globFile = BASE_PATH . '/.kite.php';
        if (file_exists($globFile)) {
            $loaded[] = $globFile;
            /** @noinspection PhpIncludeInspection */
            $userConfig = require $globFile;
            // merge to config
            $config = ArrayHelper::quickMerge($userConfig, $config);
        }

        // 当前项目配置(only for terminal)
        if ($workDir) {
            $proFile = $workDir . '/.kite.php';
            if ($proFile !== $globFile && file_exists($proFile)) {
                $loaded[] = $proFile;
                /** @noinspection PhpIncludeInspection */
                $proConfig = require $proFile;
                // merge to config
                $config = ArrayHelper::quickMerge($proConfig, $config);
            }
        }

        $config['__loaded_file'] = $loaded;
        $this->setParams($config);
    }

    /**
     * @param ObjectBox $box
     */
    protected function registerComServices(ObjectBox $box): void
    {
        $box->set('logger', function () {
            $logger = new Logger('kite');
            $handler = new RotatingFileHandler(BASE_PATH . '/tmp/kite.log');
            $logger->pushHandler($handler);

            return $logger;
        });
    }
}
