#!/usr/bin/env bash
#
# Into each dev package dir and run git update
#
#

# run: kite run --proxy upinit-kite-dev-deps.sh
# run: bash script/upinit-kite-dev-deps.sh
# run: proxy_on; bash script/upinit-kite-dev-deps.sh
set -e

# proxy_on
kiteDir=~/.kite
# kiteDir=~/workspace/phpdev/gh-repos/kite
ghHost=https://github.com
cd $kiteDir

# create array
groups=(inhere phppkg toolkit)

echo "Update kite dev depends packages."
for dir in "${groups[@]}"; do
    echo "- ✅ Update the vendor/$dir"

    ghGrp=$dir
    if [ "$dir" == "toolkit" ]; then
        ghGrp="php-$dir"
    fi

    pDir=$kiteDir/vendor/$dir
    for path in "$pDir"/*; do
        pkg=$(basename "$path")

        ghPkg=$pkg
        if [[ "$pkg" == "console" ]]; then
            ghPkg="php-$pkg"
        elif [ "$pkg" == "sroute" ]; then
            ghPkg="php-srouter"
        fi

        echo " - package: $dir/$pkg"
        if [ -d "$path"/.git ]; then
            echo "   founded the .git dir, do update"
            echo "   into $path"
            cd "$path"
            echo "   update by git pull"
            git pull
        else
            echo "   not found .git dir, do clone"
            echo "   goto $pDir"
            cd "$pDir"
            echo "   remove old package dir"
            rm -rf "$path"
            echo "   git clone $ghHost/$ghGrp/$ghPkg $pkg"
            git clone "$ghHost/$ghGrp/$ghPkg" "$pkg"
        fi
    done
done

echo "🟢 Completed"
