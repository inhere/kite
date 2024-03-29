# Kite

[![License](https://img.shields.io/packagist/l/inhere/console.svg?style=flat-square)](LICENSE)
[![Php Version](https://img.shields.io/badge/php-%3E=8.1.0-brightgreen.svg?maxAge=2592000)](https://packagist.org/packages/inhere/console)
[![zh-CN readme](https://img.shields.io/badge/中文-Readme-brightgreen.svg?maxAge=2592000)](README.md)
[![GitHub tag (latest SemVer)](https://img.shields.io/github/tag/inhere/kite)](https://github.com/inhere/kite)
[![Actions Status](https://github.com/inhere/kite/workflows/Unit-Tests/badge.svg)](https://github.com/inhere/kite/actions)

Kite is a tool for help development.

> GitHub https://github.com/inhere/kite 
> kite is build on the [inhere/php-console](https://github.com/inhere/php-console)

![kite-commands](resource/images/kite-commands.png)

## Install

**Required:**

- git
- php 8.1+
- composer

### Install by script

> file: [deploy/install.sh](deploy/install.sh)

```bash
curl https://raw.githubusercontent.com/inhere/kite/master/deploy/install.sh | bash
```

### Manual clone

```bash
cd ~
git clone https://github.com/inhere/kite .kite
cd .kite
composer install
chmod a+x bin/kite
ln -s $PWD/bin/kite /usr/local/bin/kite
```

### Download phar

- release page: https://github.com/inhere/kite/releases

eg:

```bash
wget -c https://github.com/inhere/kite/releases/download/v2.0.0/kite.phar
```

Always use latest:

```bash
wget -c https://github.com/inhere/kite/releases/latest/download/kite.phar
```

Rename and move to the environment directory:

```bash
mv kite.phar /usr/local/bin/kite
chmod a+x /usr/local/bin/kite
```

## Usage

### How to use

Display commands:

```bash
kite
kite list
```

Display help:

```bash
kite -h
kite --help
kite help
```

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
php -d phar.readonly=0 bin/kite phar:pack -o kite.phar
```

![](resource/images/build-phar.png)

## Uninstall

```bash
rm -f /usr/local/bin/kite
rm -rf ~/.kite
```

## Dep Packages

- https://github.com/inhere/php-console
- https://github.com/php-toolkit/cli-utils
- https://github.com/php-toolkit/stdlib

## Thanks

- linux command docs by https://github.com/jaywcjlove/linux-command
