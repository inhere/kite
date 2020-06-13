<?php
/**
 * Created by PhpStorm.
 * - the is a config file for compile phar package.
 * User: Inhere
 * Date: 2018/1/26 0026
 * Time: 22:11
 * @var \Inhere\Console\Component\PharCompiler $compiler
 */

// config
$compiler
  // ->stripComments(false)
  ->setShebang(true)
  ->addExclude([
    'test',
    'runtime',
    'resource',
  ])
  ->addFile([
    'LICENSE',
    'composer.json',
    'README.md',
  ])
  ->setCliIndex('bin/kite')
  // ->setWebIndex('web/index.php')
  // ->setVersionFile('config/config.php')
;
