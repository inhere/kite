<?php declare(strict_types=1);

namespace Inhere\Kite\Concern;

use Inhere\Kite\Common\GitAPI\GitHubV3API;
use Inhere\Kite\Common\GitAPI\GitLabV4API;
use Inhere\Kite\Kite;
use Inhere\Kite\Lib\Template\EasyTemplate;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Toolkit\Stdlib\Arr\ArrayHelper;
use Toolkit\Stdlib\Obj\ObjectBox;
use Toolkit\Stdlib\OS;
use Toolkit\Stdlib\Util\PhpDotEnv;
use function defined;
use function file_exists;
use function is_dir;

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
        $diskBasePath = Kite::basePath();
        $baseConfPath = $diskBasePath . '/config';
        // no config dir in disk and in phar. use phar builtin config
        if (defined('IN_PHAR') && IN_PHAR && !is_dir($baseConfPath)) {
            $baseConfPath = Kite::getPath('config', false);
        }

        // 基础配置
        $baseFile = $baseConfPath . '/config.php';
        // load config
        $config = require $baseFile;
        $loaded = [$baseFile];

        // eg: config.web.php
        $modeFile = $baseConfPath . "/config.$runMode.php";
        if (file_exists($modeFile)) {
            $loaded[]   = $modeFile;
            $modeConfig = require $modeFile;
            // merge config
            $config = ArrayHelper::quickMerge($modeConfig, $config);
        }

        // 自定义全局配置
        $globFile = $diskBasePath . '/.kite.php';
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
            $config = $this->getArrayParam('logger');
            $logger = new Logger($config['name'] ?? 'kite');

            $handler = new RotatingFileHandler($config['logfile']);
            $logger->pushHandler($handler);
            return $logger;
        });

        $box->set('txtRender', function () {
            return EasyTemplate::new()->disableEchoFilter();
        });

        $box->set('glApi', function () {
            $config = $this->getArrayParam('gitlab');

            return new GitLabV4API($config);
        });

        $box->set('ghApi', function () {
            $config = $this->getArrayParam('github');
            return new GitHubV3API($config);
        });
    }
}
