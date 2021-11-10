<?php declare(strict_types=1);

namespace Inhere\KiteTest\Lib\Template;

use Inhere\Kite\Kite;
use Inhere\Kite\Lib\Template\PhpTemplate;
use Inhere\KiteTest\BaseKiteTestCase;
use Toolkit\FsUtil\File;
use function vdump;

/**
 * class TextTemplateTest
 */
class TextTemplateTest extends BaseKiteTestCase
{
    public function testRenderFile():void
    {
        $t = new PhpTemplate();

        $tplFile = Kite::alias('@resource-tpl/gen-by-parse/gen-go-funcs.tpl');
        $tplVars = ['vars' => ['Info', 'Error', 'Warn']];

        $result = $t->renderFile($tplFile, $tplVars);

        vdump($result);
    }
}
