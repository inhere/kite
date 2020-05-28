# tmux 使用 

一些简单的使用 `tmux` 的例子

## 建立会话

创建新的会话

```bash
tmux
tmux new -s NAME
```

范例:

```bash
tmux new -s mywork
```

## 附加会话

进入到一个已经存在的会话

```bash
tmux a
tmux a -t NAME
```

范例:

```bash
tmux a -t mywork
```
