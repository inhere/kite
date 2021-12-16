#!/usr/bin/env sh

# run: sh script/update-kite-deps-by-cp.sh
set -ex

tmpKiteDir=~/Workspace/my-github/inhere/kite
usrKiteDir=~/.kite

cpDeps="cebe clue colinodell gitonomy guzzlehttp http-interop knplabs nette php-http monolog psr symfony"
for dir in $cpDeps ; do
    rm -rf $usrKiteDir/vendor/$dir
    cp -r $tmpKiteDir/vendor/$dir  $usrKiteDir/vendor/
done
