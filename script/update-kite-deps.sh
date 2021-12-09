#!/usr/bin/env sh

set -ex

#openproxy
kite env prox

tmpKiteDir=~/Workspace/my-github/inhere/kite
usrKiteDir=~/.kite

cd $tmpKiteDir || exit 2
composer update

cp $tmpKiteDir/composer.lock $usrKiteDir
cp -r $tmpKiteDir/vendor/composer $usrKiteDir/vendor/composer