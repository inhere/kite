<?php declare(strict_types=1);
/**
 * This file is part of Kite.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\Kite\Console;

use function array_merge;
use function file_exists;
use function is_array;
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

        $this->loadAppConfig();
    }

    private function loadAppConfig(): void
    {
        $curDir = $this->getInput()->getPwd();
        $ucFile = $curDir . '/.kite.inc';
        $bcFile = BASE_PATH . '/.kite.inc';
        $config = $userConfig = [];

        $loaded = [];
        if (file_exists($ucFile)) {
            $loaded[] = $ucFile;
            /** @noinspection PhpIncludeInspection */
            $userConfig = require $ucFile;
        }

        if ($ucFile !== $bcFile && file_exists($bcFile)) {
            $loaded[] = $bcFile;
            /** @noinspection PhpIncludeInspection */
            $config = require $bcFile;
        }

        $config['__loaded_file'] = $loaded;
        if ($userConfig && $config) {
            $this->mergeUserConfig($userConfig, $config);
        } else {
            $this->setConfig($config);
        }
    }

    /**
     * @param array $userConfig
     * @param array $config
     *
     * @return bool
     */
    private function mergeUserConfig(array $userConfig, array $config): bool
    {
        foreach ($userConfig as $key => $item) {
            if (isset($config[$key]) && is_array($config[$key])) {
                if (is_array($item)) {
                    $config[$key] = array_merge($config[$key], $item);
                } else {
                    $this->output->error("Array config error! the '{$key}' must be an array");
                    return false;
                }
            } else {
                // custom add/set config
                $config[$key] = $item;
            }
        }

        $this->setConfig($config);
        return true;
    }
}
