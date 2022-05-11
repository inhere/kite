#!/usr/bin/env bash

# run: kite run for-subdirs.sh

for path in vendor/*; do
    dir=$(basename "$path")
    if [[ $dir == "inhere" || $dir == "phppkg" || $dir == "toolkit" ]]; then
        echo "- Skip the vendor/$dir"
    fi
    echo "$dir"
done
