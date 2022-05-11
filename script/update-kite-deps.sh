#!/usr/bin/env bash

set -e

# run: kite run --proxy update-kite-deps.sh
# run: sh script/update-kite-deps.sh

tmpKiteDir=~/Workspace/my-github/inhere/kite
usrKiteDir=~/.kite

set -x
kite env prox
cd $tmpKiteDir || exit 2
git checkout .
git pull
composer update --no-progress
set +x

echo "update composer.lock"
cp $tmpKiteDir/composer.lock $usrKiteDir

echo "update packages:"
for path in "$tmpKiteDir"/vendor/*; do
    dir=$(basename "$path")
    if [[ $dir == "inhere" || $dir == "phppkg" || $dir == "toolkit" ]]; then
        echo "- Skip the vendor/$dir"
        continue
    fi

    echo "- Update the vendor/$dir"
    rm -rf $usrKiteDir/vendor/"$dir"
    cp -r $tmpKiteDir/vendor/"$dir"  $usrKiteDir/vendor/
done
