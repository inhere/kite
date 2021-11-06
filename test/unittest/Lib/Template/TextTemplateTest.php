<?php declare(strict_types=1);

namespace Inhere\KiteTest\Lib\Template;

use Inhere\Kite\Kite;
use Inhere\Kite\Lib\Template\TextTemplate;
use Inhere\KiteTest\BaseKiteTestCase;
use Toolkit\FsUtil\File;
use function vdump;

/**
 * class TextTemplateTest
 */
class TextTemplateTest extends BaseKiteTestCase
{
    public function testRenderByEval():void
    {
        $t = new TextTemplate();

        $tplCode = File::readAll(Kite::alias('@resource-tpl/gen-by-parse/gen-go-funcs.tpl'));
        $tplVars = ['vars' => ['Info', 'Error', 'Warn']];
        \vdump($tplCode);

        $result = $t->renderByEval($tplCode, $tplVars);

        vdump($result);
    }

    public function testRenderByRequire():void
    {
        $t = new TextTemplate();

        $tplFile = Kite::alias('@resource-tpl/gen-by-parse/gen-go-funcs.tpl');
        $tplVars = ['vars' => ['Info', 'Error', 'Warn']];
        \vdump($tplFile);

        $result = $t->renderFile($tplFile, $tplVars);

        vdump($result);
    }
}
