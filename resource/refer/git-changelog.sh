echo "changelog list by git"
#git status && git log -1 && git fetch --tags --prune --unshallow --force
git status
git log -1
git fetch --tags --prune --unshallow --force
git describe --abbrev=0 --tags
lastTag=$(git describe --abbrev=0 --tags) && git log "$lastTag"...HEAD --pretty=format:"%H | %s" --no-merges --reverse
