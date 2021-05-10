# usage: kite gen parse tmp/go-snippets-0709.tpl
vars=[Info, Error, Warn]

###

<?php foreach ($vars as $var): ?>

// <?= $var ?>f print message with <?= $var ?> style
func <?= $var ?>f(format string, a ...interface{}) {
	<?= $var ?>.Printf(format, a...)
}

<?php endforeach;?>