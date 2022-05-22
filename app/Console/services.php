<?php
/**
 * register services to cli-app
 *
 * @var Toolkit\Stdlib\Obj\ObjectBox $box
 */

use Inhere\Kite\Common\Log\CliLogProcessor;
use Inhere\Kite\Component\ScriptRunner;
use Inhere\Kite\Console\Component\AutoSetProxyEnv;
use Inhere\Kite\Console\Manager\ToolManager;
use Inhere\Kite\Console\Plugin\PluginManager;
use Inhere\Kite\Lib\Jump\QuickJump;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

// override logger, add processor
$box->set('logger', function () {
    $config = $this->config()->getArray('logger');
    $logger = new Logger($config['name'] ?? 'kite');
    $logger->pushProcessor(new CliLogProcessor());

    $handler = new RotatingFileHandler($config['logfile']);
    $logger->pushHandler($handler);
    return $logger;
}, true);

$box->set('plugManager', function () {
    $config = $this->config()->getArray('pluginManager');
    return new PluginManager($config);
});

$box->set('toolManager', function () {
    $config = $this->config()->getArray('toolManager');
    $tools = $this->config()->getArray('tools');

    $mgr = new ToolManager($config);
    $mgr->setTools($tools);

    return $mgr;
});

$box->set('scriptRunner', function () {
    $config = $this->config()->getArray('scriptRunner');
    $scripts = $this->config()->getArray('scripts');

    // create object
    $sr = new ScriptRunner($config);
    $sr->setScripts($scripts);
    $sr->scriptDirs = $this->config()->getArray('scriptDirs');

    return $sr;
});

$box->set('jumper', function () {
    $jumpConf = $this->config()->getArray('jumper');
    return QuickJump::new($jumpConf);
});

// auto proxy setting
$box->set('autoProxy', function () {
    $autoProxy = $this->config()->getArray('autoProxy');
    return AutoSetProxyEnv::new($autoProxy);
});

// $box->set('envLoader', function () {
//     $jumpConf = $this->config()->getArray('osEnv');
//     return QuickJump::new($jumpConf);
// });
