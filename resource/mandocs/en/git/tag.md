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
