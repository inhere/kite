# git log 使用

命令

```bash
git log
```

## 基本参数

这里将 log 命令的参数分为输出参数与过滤参数两种。输出参数主要有：

- `-p` ：查看提交内容的差异。
- `--abbrev-commit` ：只显示简洁 SHA-1，一般是其前 7 个字符。
- `--color` ：启用颜色。常用的颜色包括：`red, green, yellow, blue, magenta, cyan, black, white, normal`; 以及可在以上颜色之前加上格式 `bold, dim, ul, blink, reverse`. 例如：`%C(bold blue)`。
- `--graph` ：用图像的方式显示你的分支历史。
- `--stat` ：列出提交修改的文件以及一些基本修改的信息。
- `--shortstat` ：只列出修改的文件数量和修改的行数。
- `--relative-date` ：显示相对日期，即 "2 days ago" 这种格式。
- `--pretty=<option>` ：可选的 option 有 `short, full, oneline` 等。


特别地，`--pretty=format:"<format-str>"` 可以自定义显示内容，例如：

```bash
$ git log --color --pretty=format:"%Cred%h%Creset %d - %s (%cr by %an)"
36e8d6b  - Update README. (2 days ago by wklchris)
  bae6fc8  (origin/master, origin/dev, master) - Init (3 days ago by wklchris)
```

常用的选项有：


   选项    |     说明    
-----------|--------------------
`%s`       |    提交的说明文本
`%H/%h`     |  提交记录的完整/简洁 SHA-1 字符串
`%T/%t`     |  树对象的完整/简洁 SHA-1 字符串
`%P/%p`     |  父对象的完整/简洁 SHA-1 字符串
`%an/%cn`   |  作者/提交者的名字
`%ae/%ce`   |  作者/提交者的电子邮件地址
`%ad/%cd`   |  作者/提交者的修改日期（可用 `--date=` 指定格式）
`%ar/%cr`   |  作者/提交者的修改日期，以相对日期方式显示

过滤参数主要有：

* `-[num]` ：显示最近 num 次的提交，比如 `-2` 表示最近 2 次的提交。 
* `--author` ：搜索某作者的提交。
* `--commiter` ：搜索某提交者的提交。
* `--grep` ：搜索提交说明文本中包含对应内容的提交。
* `--since/--after` ：显示自从某日期以来的提交，可以是 `--since="2000-01-01“` 或者 `--since="1 year ago"` 形式。
* `--until/--before` ：显示某日期之前的提交。

> NOTICE: 过滤参数中的“搜索”使用时，默认会以逻辑“或”连接，除非添加 `--all-match` 选项。

## 比较分支间的提交

还有一种常用的 `log` 命令的操作，用于显示位于某分支但未合并到另一分支的提交。比如显示位于 dev 分支但尚未加入 master 分支的提交、以及在当前分支却不在远程仓库的提交：

```bash
   # 两点命令
   $ git log master..dev
   $ git log origin/master..HEAD
```

如果使用三点命令，则会显示只位于两分支之一的提交。通常使用 `--left-right` 选项来让 git 显示提交位于哪个分支上：

```bash
   # 三点命令
   $ git log --left-right master...dev
```

用 `^` 或者 `--not` 指明你不想查看的提交。比如，查看被 A, B 包含但不被 C 包含的提交，以下两种均可：

```bash
   $ git log refA refB ^refC
   $ git log refA refB --not refC
```

> refer link: https://self-contained.github.io/Git/Fundamentals.html#log