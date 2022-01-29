#!/usr/bin/env sh
#
# This is an script for install inhere/kite
# More please see https://github.com/inhere/kite
#
set -ex

INSTALL_DIR=~
CLONE_DIR=.kite

# goto user home dir.
cd $INSTALL_DIR || exit

# download tool by git clone
git clone https://github.com/inhere/kite $CLONE_DIR

# shellcheck disable=SC2164
cd $CLONE_DIR
# install dep packages
composer install

# add exec perm
chmod a+x bin/htu
chmod a+x bin/kite

# link bin file to ENV path
ln -s "$PWD"/bin/htu /usr/local/bin/htu
ln -s "$PWD"/bin/kite /usr/local/bin/kite

echo "- Install Successful"
kite --help
