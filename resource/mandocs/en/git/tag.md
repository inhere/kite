# Git Tag

There are some examples for use `git tag`

## Create tag

usage

    git tag -a TAG_NAME -m MESSAGE

example: 

    git tag -a v2.0.3 -m "release v2.0.3"
    git tag -a v2.0.3 -m "release v2.0.3"

## Delete tag

delete local tag:

    git tag -d TAG_NAME

delete remote tag:

    git push origin :refs/tags/TAG_NAME

## Show tags

display latest tag:

```bash
$ git describe --tags
v1.1.1-3-g437f030
```

only show tag name:

```bash
$ git describe --abbrev=0 --tags
v1.2.2
```

```bash
$ git describe --tags $(git rev-list --tags --max-count=1)
v1.2.2
```
