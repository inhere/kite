<?php declare(strict_types=1);
/**
 * This file is part of PTool.
 *
 * @link     https://github.com/inhere
 * @author   https://github.com/inhere
 * @license  MIT
 */

namespace Inhere\PTool\Console;

use function file_exists;
use const BASE_PATH;

/**
 * Class Application
 *
 * @package Inhere\PTool\Console
 */
class Application extends \Inhere\Console\Application
{
    protected function prepareRun(): void
    {
        parent::prepareRun();

        date_default_timezone_set('PRC');
    }

    protected function beforeRun(): void
    {
        $curDir = $this->getInput()->getPwd();

        $ucFile = $curDir . '/.ptool.inc';
        $bcFile = BASE_PATH . '/.ptool.inc';

        $config = [];
        if (file_exists($ucFile)) {
            /** @noinspection PhpIncludeInspection */
            $config = require $ucFile;
        } elseif (file_exists($bcFile)) {
            /** @noinspection PhpIncludeInspection */
            $config = require $bcFile;
        }

        $this->setConfig($config);
    }
}
