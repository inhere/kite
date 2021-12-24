#!/usr/bin/env sh

set -ex

# run: kite run update-kite-deps.sh --proxy
# run: sh script/update-kite-deps.sh
kite env prox

tmpKiteDir=~/Workspace/my-github/inhere/kite
usrKiteDir=~/.kite

cd $tmpKiteDir || exit 2
composer update

cp $tmpKiteDir/composer.lock $usrKiteDir
rm -rf $usrKiteDir/vendor/composer
cp -r $tmpKiteDir/vendor/composer $usrKiteDir/vendor

cpDeps="cebe clue colinodell gitonomy guzzlehttp http-interop knplabs nette php-http monolog psr symfony"
for dir in $cpDeps ; do
    rm -rf $usrKiteDir/vendor/$dir
    cp -r $tmpKiteDir/vendor/$dir  $usrKiteDir/vendor/
done
