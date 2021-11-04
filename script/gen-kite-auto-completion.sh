# gen oh-my-zsh plugin script
kite --auto-completion \
  --shell-env "$SHELL" \
  --gen-file ~/.oh-my-zsh/custom/plugins/kite/kite.plugin.zsh \
  --tpl-file resource/templates/completion/zsh.plugin.tpl

# gen completion script
#kite --auto-completion \
#  --shell-env "$SHELL" \
#  --gen-file ~/.oh-my-zsh/completions/_kite \
#  --tpl-file resource/templates/completion/zsh.tpl
