# find

- `-type` 限定类型。 如 `-type f` 仅匹配文件
  - b - 块设备文件
  - d - 目录
  - c - 字符设备文件
  - p - 管道文件
  - l - 符号链接文件
  - f - 普通文件
- `-name` 匹配名称。可以直接匹配或者模糊匹配
- `-mmin` 单位是分钟。`-mmin +10` 10分钟之前的，`-mmin -10` 最近10分钟的。
- `-mtime` 单位是天。`-mtime +1` 一天之前的，`-mtime -1` 最近一天的。
- `-exec` 后面紧跟查找后执行要的命令，最后必须以 `{} \;` 结尾。比如 `ls -atl {} \;`

## 查找一定时间前的文件

**查找10个小时前的文件**

```bash
find . -type f -mmin +600 -exec ls -atl {} \;
```

**查找10个小时前的文件并且匹配名字**

```bash
find . -type f -mmin +600 -name 'application.log.*' -exec ls -atl {} \;
```

## 更多使用


**匹配名字查找10个小时前的文件并且执行删除**

```bash
find . -type f -mmin +600 -name 'application.log.*' -exec rm -f {} \;
```

