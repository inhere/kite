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
chmod a+x bin/kite
ln -s $PWD/bin/kite /usr/local/bin/kite
```

## Usage

### All commands

```bash
kite
```

![](resource/images/kite-help.png)

## Update

### Builtin command

Use builtin command for update tool to latest

```bash
kite upself
```

### Manual update

```bash
cd ~/.kite
git pull
chmod a+x bin/kite
```

## Build Phar

```bash
php -d phar.readonly=0 bin/kite phar:pack -o=kite.phar
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
