# PTool [English](README.md)

[![GitHub tag (latest SemVer)](https://img.shields.io/github/tag/inhere/ptool)](https://github.com/inhere/ptool)

My person CLI tool package.

> Github https://github.com/inhere/ptool

**Preview:**

## 安装

**Required:**

- git
- php 7.1+
- composer

### 脚本安装

```bash
curl https://raw.githubusercontent.com/inhere/ptool/master/install.sh | bash
```

### 手动安装

```bash
cd ~
git clone https://github.com/inhere/ptool .ptool
cd .ptool
composer install
ln -s $PWD/bin/ptool /usr/local/bin/ptool
chmod a+x bin/ptool
```

## Usage

Execute:

```bash
# 1. add remote for all components
ptool git:addrmt --all

# 2. force push all change to every github repo
ptool git:fpush --all

# 3. release new version for all components
ptool git:release --all -y -t v2.0.8
```

## Update

### Builtin command

Use builtin command for update tool to latest

```bash
ptool upself
```

### Manual update

```bash
cd ~/ptool
git pull
chmod a+x bin/ptool
```

## Build Phar

> Required the `swoftcli`

```bash
php -d phar.readonly=0 ~/.composer/vendor/bin/swoftcli phar:pack -o=ptool.phar
```

## Uninstall

```bash
rm -f /usr/local/bin/ptool
rm -rf ~/.ptool
```

## Dep Packages

- https://github.com/php-toolkit/cli-utils
- https://github.com/inhere/php-console
- https://github.com/swoft-cloud/swoft-stdlib
