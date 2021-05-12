<?php declare(strict_types=1);

namespace Inhere\Kite\Common;

use Toolkit\Stdlib\Arr\ArrayHelper;

/**
 * Trait InitApplicationTrait
 *
 * @package Inhere\Kite\Common
 */
trait InitApplicationTrait
{
    protected function loadAppConfig(string $workDir = ''): void
    {
        $baseFile = BASE_PATH . '/config/config.php';
        $loaded   = [$baseFile];

        // 基础配置
        /** @noinspection PhpIncludeInspection */
        $config = require $baseFile;

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
        $this->setConfig($config);
    }

}
