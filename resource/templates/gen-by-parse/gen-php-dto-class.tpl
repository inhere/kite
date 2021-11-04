#
# usage: kite gen parse @kite/resource/example/gen-php-dto-class.tpl
#
strProps=[project, repoPath, projectId, homepage, lastCid]
arrProps=[prInfo,lastValid]
intProps=[commitNum]
class=MyClass

###

class <?= $class ?>

{

<?php foreach ($strProps as $prop): ?>
    /**
     * @param int
     */
    public $<?= $prop ?> = 0;

<?php endforeach;?>
<?php foreach ($strProps as $prop): ?>
    /**
     * @param string
     */
    public $<?= $prop ?> = '';

<?php endforeach;?>
<?php foreach ($arrProps as $prop): ?>
    /**
     * @param array
     */
    public $<?= $prop ?> = [];

<?php endforeach;?>
}