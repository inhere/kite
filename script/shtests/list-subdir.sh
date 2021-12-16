#!/usr/bin/env bash

usrKiteDir=~/.kite

#for dir in $(ls $usrKiteDir/vendor) ; do
for dir in $usrKiteDir/vendor ; do
     if [ -d "$dir"]; then
         continue
     fi
    echo $dir
done