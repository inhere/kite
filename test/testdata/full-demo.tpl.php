<?php
/**
 * usage:
 *   kite IdeaHttpFile2phpCode --file test/httptest/api-test.http --tpl full-demo.tpl.php
 *
 * @var $ctx Toolkit\Stdlib\Obj\DataObject
 * @var $req Inhere\Kite\Common\IdeaHttp\Request
 * @var $url Inhere\Kite\Common\IdeaHttp\UrlInfo
 * @var $body Inhere\Kite\Common\IdeaHttp\BodyData
 */
?>
    /**
     * <?= $req->getTitle(true) ?>
     *
     * @throws Throwable
     * @api <?= $req->getUrlInfo()->getPath(true) ?>
     */
    public function action<?= $req->getUrlInfo()->getShortName(true)?>(): void
    {
        $post = Yii::$app->request->post();

<?php foreach ($body->getArrayCopy() as $key => $val) : ?>
        $<?= $key?> = Validate::validate<?php
    $typ = $body->getValType($val); echo ucfirst($typ === 'array' ? 'arrayValue' : $typ)
?>($post, '<?= $key?>');
<?php endforeach ?>

<?php

$a = random_int(1, 10);
?>

<?php if ($a < 3): ?>
  at if
<?php elseif ($a > 5) : ?>
  at elseif
<?php else : ?>
  at else
<?php endif ?>

        $logic = new <?= ucfirst($ctx->getString('resName'))?>Logic();

        $result = $logic-><?= $url->getShortName()?>($id, $params);
        ResponseHelper::outputJson($result);
    }
