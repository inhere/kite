# vim 快速使用

## plugin manager

- vim-plug

## search plugins

- https://vimawesome.com

## 快速使用

- `w`（forward）键会将光标一次向前跳过一个单词
- `b` 键的意思是块，但当你单独使用它时，它的意思是返回（back），并且每次向后移动光标一个单词

## 进阶使用

大多数命令有两个、三个或四个部分。

三部分结构的一个版本是这样的：操作符（operator）- 文本对象（text object）- 动作（motion）。

操作符包括删除（delete）、更改（change）、视觉选择（visual select）和替换（replace），每次选一个使用。
文本对象要么在内部（inside）要么在周围（around）。

动作有很多种，我们稍后会具体讨论，现在我们可以把动作看作是命令的一种目标。

> 举个例子，我可以按 `dib`，意思是在块内删除（delete inside block）。
其中操作符是 delete，文本对象是 inside，动作是 block。这样就可以删除一个（括号）块内的所有内容。

可选的组合数量很多：
- `di'` ——删除（delete）`单引号'` 内（inside）的内容。
- `da"` ——删除 `双引号"` 周围（around）的内容。
- `dit` —— 删除 html 标签（tag）内的内容。
- `ci[` —— 改变（change）`[方括号]` 内的内容。

正如我前面所说的，可供选择的动作命令有很多，它们的表现也各不相同，具体取决于你是在三部分组合中
使用（如上所述），还是在两部分组合中使用（这时去掉文本对象，让命令从光标位置向后运行）。

下面是你可以在上述三段式组合中使用的一些相关动作的清单。

```text
--------------------------------------------------
| motions                           | key        |
|-----------------------------------|------------|
| word                              | w          |
| WORD (includes special chars)     | W          |
| block (of parentheses)            | b or (     |
| BLOCK (of curly braces)           | B or {     |
| brackets                          | [          |
| single quotes                     | '          |
| double quotes                     | "          |
| tag (html or xml tag)             | t          |
| paragraph                         | p          |
| sentence                          | s          |
--------------------------------------------------
```