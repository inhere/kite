<?php declare(strict_types=1);

namespace Inhere\Kite\Common\Traits;

use Inhere\Kite\Common\GitAPI\GitHubV3API;
use Inhere\Kite\Common\GitAPI\GitLabV4API;
use Inhere\Kite\Kite;
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
    /**
     * Load .env settings
     * should call it before loadAppConfig()
     */
    protected function loadEnvSettings(): void
    {
        $loader = PhpDotEnv::global();
        // kite root dir
        $loader->add(Kite::getPath('.env'));

        // user homedir
        $loader->add(OS::userConfigDir('.kite.env'));
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
        $config = require $baseFile;

        // eg: config.web.php
        $modeFile = BASE_PATH . "/config/config.$runMode.php";
        if (file_exists($modeFile)) {
            $loaded[]   = $modeFile;
            $modeConfig = require $modeFile;
            // merge config
            $config = ArrayHelper::quickMerge($modeConfig, $config);
        }

        // 自定义全局配置
        $globFile = BASE_PATH . '/.kite.php';
        if (file_exists($globFile)) {
            $loaded[]   = $globFile;
            $userConfig = require $globFile;
            // merge config
            $config = ArrayHelper::quickMerge($userConfig, $config);
        }

        // 当前项目配置(only for terminal)
        if ($workDir) {
            $proFile = $workDir . '/.kite.php';
            if ($proFile !== $globFile && file_exists($proFile)) {
                $loaded[]  = $proFile;
                $proConfig = require $proFile;
                // merge config
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
            $config = $this->getParam('logger', []);
            $logger = new Logger($config['name'] ?? 'kite');

            $handler = new RotatingFileHandler($config['logfile']);
            $logger->pushHandler($handler);
            return $logger;
        });

        $box->set('glApi', function () {
            $config = $this->getParam('gitlab', []);

            return new GitLabV4API($config);
        });

        $box->set('ghApi', function () {
            $config = $this->getParam('github', []);
            return new GitHubV3API($config);
        });
    }
}
