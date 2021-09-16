#!/usr/bin/env bash
# ------------------------------------------------------------------------------
#          DATE:  {{datetime}}
#          FILE:  {{filename}}
#        AUTHOR:  inhere (https://github.com/inhere)
#       VERSION:  {{version}}
#   DESCRIPTION:  bash shell complete for console app: {{binName}}
# ------------------------------------------------------------------------------
#
# temp usage:
#   source {{filename}}
# add to ~/.bashrc:
#   source path/to/{{filename}}
# run 'complete' to see registered complete function.

_complete_for_{{fmtBinName}} () {
    local cur prev
    commands="{{commands}}"
    COMPREPLY=($(compgen -W "$commands" -- "$cur"))
}

# complete -o bashdefault -o default -o nospace -F _complete_for_{{fmtBinName}} {{binName}}
# complete -o dirnames -F _complete_for_{{fmtBinName}} {{binName}}
complete -o dirnames -o plusdirs -F _complete_for_{{fmtBinName}} {{binName}}

## aliases for {{binName}}
#alias kj="kite jump"
alias kg="kite git"
alias cht="kite cheat"
alias kgit="kite git"
alias kgl="kite gitlab"
alias kgh="kite github"