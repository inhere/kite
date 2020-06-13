# Kite [English](README.md)

[![GitHub tag (latest SemVer)](https://img.shields.io/github/tag/inhere/kite)](https://github.com/inhere/kite)

My person CLI tool package.

> Github https://github.com/inhere/kite

**Preview:**

## 安装

**Required:**

- git
- php 7.1+
- composer

### 脚本安装

```bash
curl https://raw.githubusercontent.com/inhere/kite/master/install.sh | bash
```

### 手动安装

```bash
cd ~
git clone https://github.com/inhere/kite .kite
cd .kite
composer install
ln -s $PWD/bin/kite /usr/local/bin/kite
chmod a+x bin/kite
```

## 使用

```bash
kite
```

![](resource/images/kite-help.png)

## 更新

### 内置命令

Use builtin command for update tool to latest

```bash
kite upself
```

### 手动更新

```bash
cd ~/.kite
git pull
chmod a+x bin/kite
```

## 构建Phar包

```bash
php -d phar.readonly=0 bin/kite phar:pack
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
