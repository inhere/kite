#!/usr/bin/env bash

# run: kite run dev-update-kite-deps.sh --proxy

set -ex

# proxy_on

kiteDir=~/.kite
cd $kiteDir

(cd vendor/inhere/console;git pull)

(cd vendor/phppkg/easytpl;git pull)

(cd vendor/toolkit/stdlib;git pull)

(cd vendor/toolkit/cli-utils; git pull)

(cd vendor/toolkit/fsutil; git pull)

(cd vendor/toolkit/pflag; git pull)

(cd vendor/toolkit/sys-utils; git pull)
