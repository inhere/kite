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
    local IFS=$'\n'
    commands+=(
{{commands}}
    )

    _describe 'commands' commands
    _alternative \
        'files:filename:_files'

    case "\$words[1]" in
        (git)
            _arguments \
                -v'[Verbose (more) output]'
        ;;
    esac
}

compdef _complete_for_{{fmtBinName}} {{binName}}

## aliases for {{binName}}
# NOTICE: zsh plugin support add aliases
#alias kj="kite jump"
alias kg="kite git"
alias kgit="kite git"
alias kjson="kite json"
alias kplug="kite plugin"
alias kstr="kite string"
alias kgl="kite gitlab"
alias kgh="kite github"
alias cht="kite cheat"
