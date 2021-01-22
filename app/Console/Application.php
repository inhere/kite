<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console;

use Inhere\Kite\Kite;
use Toolkit\Stdlib\Arr\ArrayHelper;
use function file_exists;
use const BASE_PATH;

/**
 * Class Application
 *
 * @package Inhere\Kite\Console
 */
class Application extends \Inhere\Console\Application
{
    protected function prepareRun(): void
    {
        parent::prepareRun();

        date_default_timezone_set('PRC');
    }

    protected function init(): void
    {
        parent::init();

        Kite::setCliApp($this);

        $this->loadAppConfig();
    }

    private function loadAppConfig(): void
    {
        $baseFile = BASE_PATH . '/config/config.php';
        $loaded = [$baseFile];

        // 基础配置
        /** @noinspection PhpIncludeInspection */
        $config = require $baseFile;

        // 自定义全局配置
        $globFile = BASE_PATH . '/.kite.inc';
        if (file_exists($globFile)) {
            $loaded[] = $globFile;
            /** @noinspection PhpIncludeInspection */
            $userConfig = require $globFile;
            // merge to config
            $config = ArrayHelper::quickMerge($userConfig, $config);
        }

        // 当前项目配置
        $workDir = $this->getInput()->getPwd();
        $proFile = $workDir . '/.kite.inc';
        if ($proFile !== $globFile && file_exists($proFile)) {
            $loaded[] = $proFile;
            /** @noinspection PhpIncludeInspection */
            $proConfig = require $proFile;
            // merge to config
            $config = ArrayHelper::quickMerge($proConfig, $config);
        }

        $config['__loaded_file'] = $loaded;
        $this->setConfig($config);
    }
}
