#!/usr/bin/env bash
#
# - update composer.lock
# - update all depends package
# - skip .git existed dev package
# - clone no .git existed dev package
#

# run: kitep run --proxy update-kite-deps.sh
# run: bash script/update-kite-deps.sh
# run: proxy_on; bash script/update-kite-deps.sh

set -e

osName=$(uname -s)
usrKiteDir=~/.kite

# macOS
if [ "$osName" == "Darwin" ]; then
    tmpKiteDir=~/Workspace/my-github/inhere/kite
# windows: MINGW64_NT-10.0-19043
elif [ "${osName:0:5}" == "MINGW" ]; then
    tmpKiteDir=/c/Users/inhere/workspace/phpdev/tmp/kite-latest
    usrKiteDir=/c/Users/inhere/workspace/phpdev/gh-repos/kite
else
    tmpKiteDir=/tmp/kite-tmp
fi

set -x
if ! test -d $tmpKiteDir; then
    echo "- Source dir not exist, clone kite ..."
    mkdir -p $tmpKiteDir
    git clone https://github.com/inhere/kite $tmpKiteDir
fi

#kite env proxy
cd $tmpKiteDir || exit 2
git checkout .
git pull
composer update --no-progress --ignore-platform-req=ext-posix
set +x

echo "✅  Update composer.lock"
cp $tmpKiteDir/composer.lock $usrKiteDir

echo "Update depends packages:"
for path in "$tmpKiteDir"/vendor/*; do
    dir=$(basename "$path")

    # update kite dev packages
    if [[ $dir == "inhere" || $dir == "phppkg" || $dir == "toolkit" ]]; then
        for subpath in "$path"/* ; do
            pkg=$(basename "$subpath")
            dstDir=$usrKiteDir/vendor/"$dir/$pkg"
            if [ -d "$dstDir"/.git ]; then
                echo "- 🙈 SKIP exist dev package $dir/$pkg"
            else
                echo "- ✅  Add new dev package: $dir/$pkg"
                cp -r "$tmpKiteDir/vendor/$dir/$pkg"  "$usrKiteDir/vendor/$dir"/
            fi
        done
        continue
    fi

    # composer autoload files
    if [ "$dir" == "autoload.php" ]; then
        echo "- ✅  Update the vendor/$dir"
        cp $tmpKiteDir/vendor/"$dir" $usrKiteDir/vendor/
        continue
    fi
    if [[ "$dir" == "bin" || "$dir" == "composer" ]]; then
        echo "- ✅  Update the vendor/$dir"
        rm -rf $usrKiteDir/vendor/"$dir"
        cp -r $tmpKiteDir/vendor/"$dir" $usrKiteDir/vendor/
        continue
    fi

    # update third packages
    for subpath in "$path"/* ; do
        pkg=$(basename "$subpath")
        echo "- ✅  Update package: $dir/$pkg"
        rm -rf $usrKiteDir/vendor/"$dir/$pkg"
        cp -r $tmpKiteDir/vendor/"$dir/$pkg"  $usrKiteDir/vendor/"$dir"/
    done
done

echo "🟢 Completed"

function foo() {
    return
}
