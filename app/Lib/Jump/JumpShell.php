<?php declare(strict_types=1);

namespace Inhere\Kite\Lib\Jump;

use RuntimeException;
use function in_array;

/**
 * Class JumpDirShell
 *
 * @package Inhere\Kite\Lib
 */
class JumpShell
{
    public const SUPPORTED = [self::NAME_BASH, self::NAME_ZSH];

    public const NAME_BASH = 'bash';
    public const NAME_ZSH  = 'zsh';

    /**
     * @link https://github.com/gsamokovarov/jump/blob/main/shell/bash.go
     */
    public const BASH_SCRIPT = <<<BASH
# Put the line below in ~/.bashrc or ~/bash_profile:
#
#   eval "$(kite jump shell bash)"
#   # set the bind func name is: j
#   eval "$(kite jump shell bash --bind j)"
#
# The following lines are autogenerated:
__jump_prompt_command() {
  local status=$?
  kite jump chdir && return \$status
}
__jump_hint() {
  local term="\${COMP_LINE/#{{bindFunc}} /}"
  echo \'$(kite jump hint "\$term")\'
}
{{bindFunc}}() {
  local dir
  dir="$(kite jump get "$@")"
  test -d "\$dir" && cd "\$dir"
}

[[ "\$PROMPT_COMMAND" =~ __jump_prompt_command ]] || {
  PROMPT_COMMAND="__jump_prompt_command;\$PROMPT_COMMAND"
}
complete -o dirnames -C '__jump_hint' {{bindFunc}}

# add alias for: kite jump
alias kj="kite jump"
BASH;

    /**
     * @link https://github.com/gsamokovarov/jump/blob/main/shell/zsh.go
     */
    public const ZSH_SCRIPT = <<<ZSH
# Put the line below in ~/.zshrc:
#
#   eval "$(kite jump shell zsh)"
#   # set the bind func name is: j
#   eval "$(kite jump shell zsh --bind j)"
#
# The following lines are autogenerated:

# change pwd hook
__jump_chpwd() {
  kite jump chdir
}

typeset -gaU chpwd_functions
chpwd_functions+=(__jump_chpwd)

_jump_completion() {
    # reply=('test1' 'test2')
    # reply="'$(kite jump hint "$@")'"
    typeset -a commands

    # commands+=($(kite jump hint "$@"))
    commands+=($(kite jump hint "\$words[2]"))
    # _describe -t 'commands' commands
    _describe 'commands' commands
    _alternative \
        'files:filename:_files'
}

{{bindFunc}}() {
  local dir
  dir=$(kite jump get "$@")
  test -d "\$dir" && cd "\$dir"
}

# for use `reply`
# compctl -U -K _jump_completion {{bindFunc}}
# for use `_describe`
compdef _jump_completion '{{bindFunc}}'
ZSH;

    /**
     * @param string $shell
     *
     * @return string
     */
    public static function getShellScript(string $shell): string
    {
        if ($shell === self::NAME_BASH) {
            return self::BASH_SCRIPT;
        }

        if ($shell === self::NAME_ZSH) {
            return self::ZSH_SCRIPT;
        }

        throw new RuntimeException("not supported shell env name: $shell");
    }

    /**
     * @param string $shell
     */
    public static function checkShellName(string $shell): void
    {
        if (!self::isSupported($shell)) {
            throw new RuntimeException("not supported shell env name: $shell");
        }
    }

    /**
     * @param string $shell
     *
     * @return bool
     */
    public static function isSupported(string $shell): bool
    {
        return in_array($shell, self::SUPPORTED, true);
    }
}
