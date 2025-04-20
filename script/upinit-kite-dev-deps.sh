#!/usr/bin/env bash
#
# Into each dev package dir and run git update
#
#
# run: kitep run --proxy upinit-kite-dev-deps.sh
# run: kite run --proxy bash script/upinit-kite-dev-deps.sh
#
# run: bash script/upinit-kite-dev-deps.sh
# run: proxy_on; bash script/upinit-kite-dev-deps.sh
set -e


osName=$(uname -s)
#usrKiteDir=~/.kite

# macOS
if [ "$osName" == "Darwin" ]; then
    kiteSrcDir=~/.kite
# windows: MINGW64_NT-10.0-19043
elif [ "${osName:0:5}" == "MINGW" ]; then
    kiteSrcDir=~/.kite
    # kiteSrcDir=/f/work/php/inhere/kite-tmp
else
    kiteSrcDir=/tmp/kite-tmp
fi

# proxy_on
ghHost=https://github.com
echo "Goto kite src dir: $kiteSrcDir"
cd $kiteSrcDir

# create array
groups=(inhere phppkg toolkit)

echo "Update kite dev depends packages."
for dir in "${groups[@]}"; do
    echo "✅ Update the vendor/$dir"

    ghGrp=$dir
    if [ "$dir" == "toolkit" ]; then
        ghGrp="php-$dir"
    fi

    pDir=$kiteSrcDir/vendor/$dir
    for path in "$pDir"/*; do
        pkg=$(basename "$path")

        ghPkg=$pkg
        if [[ "$pkg" == "console" ]]; then
            ghPkg="php-$pkg"
        elif [ "$pkg" == "sroute" ]; then
            ghPkg="php-srouter"
        fi

        echo "Package: 【$dir/$pkg】"
        if [ -d "$path"/.git ]; then
            echo "   founded the .git dir, do update"
            echo "   into $path"
            cd "$path"
            echo "   【update】 by git pull"
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
        echo '-------------------------------------------------------------------'
    done
done

echo "🟢 Completed"
