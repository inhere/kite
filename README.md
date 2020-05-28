# Kite [中文说明](README.zh-CN.md)

[![GitHub tag (latest SemVer)](https://img.shields.io/github/tag/inhere/kite)](https://github.com/inhere/kite)

My person CLI tool package.

> Github https://github.com/inhere/kite

**Preview:**

## Install

**Required:**

- git
- php 7.1+
- composer

### Install by script

```bash
curl https://raw.githubusercontent.com/inhere/kite/master/install.sh | bash
```

### Manual install

```bash
cd ~
git clone https://github.com/inhere/kite .kite
cd .kite
composer install
ln -s $PWD/bin/kite /usr/local/bin/kite
chmod a+x bin/kite
```

## Usage

First use git to pull the latest swoft-components or swoft-ext to the local, go to the repository directory.

Execute:

```bash
# 1. add remote for all components
kite git:addrmt --all

# 2. force push all change to every github repo
kite git:fpush --all

# 3. release new version for all components
kite git:release --all -y -t v2.0.8
```

## Update

### Builtin command

Use builtin command for update tool to latest

```bash
kite upself
```

### Manual update

```bash
cd ~/kite
git pull
chmod a+x bin/kite
```

## Build Phar

> Required the `swoftcli`

```bash
php -d phar.readonly=0 ~/.composer/vendor/bin/swoftcli phar:pack -o=kite.phar
```

## Uninstall

```bash
rm -f /usr/local/bin/kite
rm -rf ~/.kite
```

## Dep Packages

- https://github.com/php-toolkit/cli-utils
- https://github.com/inhere/php-console
- https://github.com/swoft-cloud/swoft-stdlib
