<?php declare(strict_types=1);

namespace Inhere\Kite\Concern;

use Inhere\Kite\Common\GitAPI\GitHubV3API;
use Inhere\Kite\Common\GitAPI\GitLabV4API;
use Inhere\Kite\Kite;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use PhpPkg\Config\ConfigBox;
use PhpPkg\EasyTpl\EasyTemplate;
use PhpPkg\JenkinsClient\MultiJenkins;
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

        Kite::config()->loadData($config);
        $this->setParams(Kite::config()->getArray('app'));
    }

    /**
     * @param ObjectBox $box
     */
    protected function registerComServices(ObjectBox $box): void
    {
        $box->set('logger', function () {
            $config = $this->config()->getArray('logger');
            $logger = new Logger($config['name'] ?? 'kite');

            $handler = new RotatingFileHandler($config['logfile']);
            $logger->pushHandler($handler);
            return $logger;
        });

        $box->set('txtRender', function () {
            return EasyTemplate::textTemplate()->disableEchoFilter();
        });

        $box->set('htmlRender', function () {
            return EasyTemplate::new();
        });

        $box->set('glApi', function () {
            $config = $this->config()->getArray('gitlab');

            return new GitLabV4API($config);
        });

        $box->set('ghApi', function () {
            $config = $this->config()->getArray('github');
            return new GitHubV3API($config);
        });

        $box->set('jenkins', function () {
            $config = $this->config()->getArray('jenkins');
            return new MultiJenkins($config);
        });
    }

    /**
     * @return ConfigBox
     */
    public function config(): ConfigBox
    {
        return Kite::config();
    }
}
