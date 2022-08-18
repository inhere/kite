<?php
// kite mycmd.php
// kite mycmd.php [opts] [args]

use Toolkit\Cli\Cli;
use Toolkit\PFlag\CliCmd;
use Toolkit\PFlag\FlagsParser;

// load kite boot file, allow use all class in the kite.
$kiteBootFile = getenv('KITE_BOOT_FILE');
if (!$kiteBootFile || !is_file($kiteBootFile)) {
    throw new RuntimeException("kite boot file is not exists", 1);
}

echo "... load kite boot file: ", $kiteBootFile, "\n\n";
require $kiteBootFile;

// can use toolkit/pflag, more please see https://github.com/php-toolkit/pflag

CliCmd::new()
      ->config(function (CliCmd $cmd) {
          $cmd->name = 'cgen';
          $cmd->desc = 'quickly generate a blog or doc markdown file';

          // config flags
          $cmd->options = [
              'i,interactive' => 'bool;interactive set config',
              't,tpl-file'     => 'the tempalte file;;',
          ];
          // or use property
          // $cmd->arguments = [...];
      })
    // ->withArguments([
    //     'arg1' => 'this is arg1, is string'
    // ])
      ->setHandler('handle_func')
      ->run();

function handle_func(FlagsParser $fs)
{
    Cli::info("hello");
    vdump(
        $fs->getScriptFile() . implode(' ', $fs->getFlags()),
        $fs->getOpts()
    );
}
