# Kite

[![License](https://img.shields.io/packagist/l/inhere/console.svg?style=flat-square)](LICENSE)
[![Php Version](https://img.shields.io/badge/php-%3E=7.2.0-brightgreen.svg?maxAge=2592000)](https://packagist.org/packages/inhere/console)
[![GitHub tag (latest SemVer)](https://img.shields.io/github/tag/inhere/kite)](https://github.com/inhere/kite)
[![Actions Status](https://github.com/inhere/kite/workflows/Unit-Tests/badge.svg)](https://github.com/inhere/kite/actions)

PHP编写的个人CLI工具包

> Github https://github.com/inhere/kite

## [English](README.md)

## 安装

**系统环境依赖**

- git
- php 7.1+
- composer

**脚本安装**

> file: [install.sh](./install.sh)

```bash
curl https://raw.githubusercontent.com/inhere/kite/master/install.sh | bash
```

**手动安装**

```bash
cd ~
git clone https://github.com/inhere/kite .kite
cd .kite
composer install
ln -s $PWD/bin/kite /usr/local/bin/kite
chmod a+x bin/kite
```

**下载PHAR**

- Release page: https://github.com/inhere/kite/releases

注意替换为最新的版本号:

```bash
wget -c https://github.com/inhere/kite/releases/download/v1.0.5/kite-v1.0.5.phar
mv kite-v1.0.5.phar /usr/local/bin/kite
chmod a+x /usr/local/bin/kite
```

## 使用说明

## 查看命令帮助

```bash
kite -h
```

![](resource/images/kite-help.png)

## Git 常用命令

```bash
kite git {command} [arguments ...] [--options ...]
```

## Gitlab 常用命令

命令：`gitlab` (别名： `gl`)

```bash
kite gitlab {command} [arguments ...] [--options ...]
```

**查看命令列表**

![](resource/images/kite-gitlab-help.png)


**浏览器打开仓库**

在项目所在目录执行如下命令，即可自动使用默认浏览器打开仓库页面

```bash
kite gl open
```


## 其他工具命令

**env**

显示环境变量信息：

```bash
kite env
```

输出 `PATH` 信息：

```bash
kite env path
```

## 使用简单脚本

## 命令别名配置

## 更新

### 内置命令

使用内置命令将更新工具更新到最新版本

```bash
kite selfupdate
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
- https://github.com/php-toolkit/sys-utils
- https://github.com/php-toolkit/stdlib
- https://github.com/ulue/phpgit

## Thanks

- linux command docs by https://github.com/jaywcjlove/linux-command
