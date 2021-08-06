# ------------------------------------------------------------------------------
#          DATE:  {{datetime}}
#          FILE:  {{filename}}
#        AUTHOR:  inhere (https://github.com/inhere)
#       VERSION:  {{version}}
#   DESCRIPTION:  zsh shell completion for console app: {{binName}}
# ------------------------------------------------------------------------------
#
# temp usage:
#   source {{filename}}
# add to ~/.zshrc:
#   source path/to/{{filename}}

_complete_for_{{fmtBinName}} () {
    local -a commands
    IFS=$'\n'
    commands+=(
{{commands}}
    )

    _describe 'commands' commands
}

compdef _complete_for_{{fmtBinName}} {{binName}}

## aliases for {{binName}}
# NOTICE: zsh plugin support add aliases
#alias kj="kite jump"
alias kgit="kite git"
