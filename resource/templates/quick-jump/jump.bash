# Put the line below in ~/.bashrc or ~/bash_profile:
#
#   eval "$(kite jump shell bash)"
#   # set the bind func name is: j
#   eval "$(kite jump shell bash --bind j)"
#
# The following lines are autogenerated:

# hooks on dir changed
__jump_prompt_command() {
    # eg: "j" or "j /path/to/dir"
    local lastCmd=$(history 1 | {
        read x cmd args
        echo "$cmd"
    })

    # kite util log "lastCmd $lastCmd" --type bash-jump-chdir
    # Do not process other commands executed
    if [[ $lastCmd != "{{bindFunc}}" ]]; then
        return 0
    fi

    local status=$?
    kite jump chdir --quiet && return $status
}

# will call func on every command exec.
[[ "$PROMPT_COMMAND" =~ __jump_prompt_command ]] || {
    PROMPT_COMMAND="__jump_prompt_command;$PROMPT_COMMAND"
}

# completion func for {{bindFunc}}
# refer https://blog.csdn.net/qq_38883889/article/details/106543271
__jump_completion() {
    # example:
    # input 'jump hi'
    # - $COMP_LINE='jump hi'
    # - $term='hi'
    local term="${COMP_WORDS[COMP_CWORD]}"

    local commands=$(kite jump hint "$term" --no-name)
    #    echo commands:
    #    echo $commands --- ${commands[@]}
    #    COMPREPLY=$commands
    COMPREPLY=($commands)
#    echo $COMPREPLY
#    echo ${COMPREPLY[2]}
#    echo all --- ${COMPREPLY[@]}
    #    COMPREPLY=("${commands[@]}")
    return
}

{{bindFunc}}() {
    local dir
    dir=$(kite jump get "$@")
    test -d "$dir" && cd "$dir"
}

# for use echo
#complete -o dirnames -C '__jump_completion' {{bindFunc}}
# for use COMPREPLY
complete -o dirnames -o plusdirs -F __jump_completion {{bindFunc}}

# add alias for: kite jump
alias kj="kite jump"
