#
#
# use for custom config an .bashrc, support auto load *.bash files
#

 ## load bash files
for file in ~/.config/bash-completions/*.bash
do
    # shellcheck disable=SC1090
    test -f $file && . $file
done

## load custom aliases file.
# shellcheck disable=SC1090
test -f ~/.config/.my-aliases.sh && . ~/.config/.my-aliases.sh
