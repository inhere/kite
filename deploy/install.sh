#!/usr/bin/env sh
#
# This is an script for install inhere/kite
# More please see https://github.com/inhere/kite
#
# local run: bash ./deploy/install.sh
set -e

INSTALL_DIR=~
CLONE_DIR=.kite

# goto user home dir.
cd $INSTALL_DIR || exit

echo "🔄 Check install depends: git, php, composer"
if ! [ -x "$(command -v git)" ]; then
  echo '🔴 Error: git is not installed.'
  exit 1
fi

if ! [ -x "$(command -v php5)" ]; then
  echo '🔴 Error: php is not installed.'
  exit 1
fi

if ! [ -x "$(command -v composer)" ]; then
  echo '🔴 Error: composer is not installed.'
  exit 1
fi

if [ -d "$INSTALL_DIR/$CLONE_DIR"/bin ]; then
    echo "🙈 SKIP install, the kite dir exists"
    exit
fi

echo "🟢  Fetch kite codes by git clone"
# download tool by git clone
git clone https://github.com/inhere/kite $CLONE_DIR

echo "🟢  Install deps by composer"
# shellcheck disable=SC2164
cd $CLONE_DIR
# install dep packages
composer install

echo "🟢  Initialize kite"
set -x

# add exec perm
chmod a+x bin/htu
chmod a+x bin/kite

# init user config
cp .kite.example.php .kite.php

# link bin file to ENV path
#sudo ln -s "$PWD"/bin/htu /usr/local/bin/htu
sudo ln -s "$PWD"/bin/kite /usr/local/bin/kite
set +x

echo "✅  Install successful"
kite --version
