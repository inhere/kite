<?php
// kite ls.php
// kite ls.php [DIR]

use Toolkit\FsUtil\FileFinder;

if ($kiteDir = getenv('KITE_PATH')) {
    require $kiteDir. '/app/boot.php';
}

$args = $_SERVER['argv'];

FileFinder::create()
    ->in($args[1] ?? '.')
    ->onlyFiles()
    ->name('*.php')
    ->each(function (SplFileInfo $f) {
        printf("%s\n", $f->getPathname());
    });
