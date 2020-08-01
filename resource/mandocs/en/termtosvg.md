# termtosvg

`termtosvg` Record your terminal session as a single SVG animation

- github https://github.com/nbedos/termtosvg

## Install

install by brew

```bash
brew install termtosvg
```

## Usage

quick usage examples:

start:

```bash
termtosvg
termtosvg coustom-name.svg
```

end:

```bash
exit
```

## more examples

with a theme:

```bash
termtosvg -t solarized_light
```

with width and height:

```bash
termtosvg -g 80x24 animation.svg
```

more arguments:

```bash
termtosvg -t solarized_light -g 90x5  _examples/images/progress/bar.svg
```
