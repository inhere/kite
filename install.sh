#!/usr/bin/env sh
#
# This is an script for install inhere/kite
# More please see https://github.com/inhere/kite
#
set -ex

cd ~ || exit
# download tool
git clone https://github.com/inhere/kite .kite
# shellcheck disable=SC2164
cd .kite
# install dep packages
composer install
# add exec perm
chmod a+x bin/htu
chmod a+x bin/kite
# mv to env path
ln -s "$PWD"/bin/htu /usr/local/bin/htu
ln -s "$PWD"/bin/kite /usr/local/bin/kite
