#compdef kite
# ------------------------------------------------------------------------------
#          DATE:  2021-08-04 13:45:10
#          FILE:  auto-completion.zsh
#        AUTHOR:  inhere (https://github.com/inhere)
#       VERSION:  1.0.5
#   DESCRIPTION:  zsh shell complete for console app: kite
# ------------------------------------------------------------------------------
# usage: source auto-completion.zsh

_complete_for_kite () {
    local -a commands
    IFS=$'\n'
    commands+=(
'version:Show application version information'
'help:Show application help information'
'list:List all group and alone commands'
'convert:Some useful convert development tool commands'
'crontab:parse or convert crontab expression [alias\: cron]'
'db:Database development tool commands'
'file:Some useful development tool commands [alias\: fs]'
'gen:quick generate new class or file from template [alias\: generate]'
'git:Provide useful tool commands for quick use git [alias\: g]'
'gitflow:Some useful tool commands for git flow development [alias\: gf]'
'github:Some useful development tool commands [alias\: gh,hub]'
'gitlab:Some useful tool commands for gitlab development [alias\: gl]'
'go:Some useful tool commands for go development'
'jump:Jump helps you navigate faster by learning your habits. [alias\: goto]'
'k8s:Kubernetes development tool commands'
'new:quick create new project or package or library tool commands [alias\: create]'
'phar:Pack a project directory to phar or unpack phar to directory'
'php:Some useful tool commands for php development'
'plugin:kite plugins manage tools [alias\: plugins,plug]'
'self:Operate and manage kite self commands'
'snippet:Some useful development tool commands [alias\: snippets,snip]'
'sys:Some useful tool commands for system'
'util:Some useful development tool commands'
'cheat:Query cheat for development [alias\: cht,cht.sh,cheat.sh]'
'doc:Useful documents for how to use git,tmux and more tool [alias\: man,docs]'
'env:a test command'
'expr:Use for expression calculation [alias\: calc]'
'find:find file content by grep command [alias\: grep]'
'init:a test command'
'json5:read and convert json5 file to json format [alias\: j5]'
'markdown:render markdown file on terminal [alias\: md,mkdown]'
'run:run an script command in the "scripts" [alias\: exec,script]'
    )

    _describe 'commands' commands
}

compdef _complete_for_kite kite
