#!/usr/bin/env sh
#
# This is an scrpit for install ptool
# More please see https://github.com/inhere/ptool
#
set -ex

cd ~ || exit
# download tool
git clone https://github.com/inhere/ptool .ptool
# shellcheck disable=SC2164
cd .ptool
# intall dep packages
composer install
# add exec perm
chmod a+x bin/ptool
# mv to env path
ln -s "$PWD"/bin/ptool /usr/local/bin/ptool
