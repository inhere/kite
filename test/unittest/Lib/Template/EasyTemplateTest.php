<?php declare(strict_types=1);

namespace Inhere\KiteTest\Lib\Template;

use Inhere\Kite\Kite;
use Inhere\Kite\Lib\Template\Compiler\Token;
use Inhere\Kite\Lib\Template\EasyTemplate;
use Inhere\KiteTest\BaseKiteTestCase;
use PhpToken;
use Toolkit\FsUtil\File;
use function preg_match;
use function random_int;
use function vdump;

/**
 * class EasyTemplateTest
 */
class EasyTemplateTest extends BaseKiteTestCase
{
    public function testV2Render_use_echo_foreach():void
    {
        $t = new EasyTemplate();

        $tplFile = Kite::resolve('@testdata/use_echo_foreach.tpl');
        $tplVars = ['vars' => ['Info', 'Error', 'Warn']];

        $result = $t->renderFile($tplFile, $tplVars);
        $this->assertNotEmpty($result);

        vdump($result);
    }

    public function testCompileFile_use_echo_foreach():void
    {
        $t = new EasyTemplate();

        $tplFile = Kite::resolve('@testdata/use_echo_foreach.tpl');
        $phpFile = $t->compileFile($tplFile);

        $this->assertNotEmpty($phpFile);

        $genCode = File::readAll($phpFile);

        $this->assertStringContainsString('<?php', $genCode);
        $this->assertStringContainsString('<?=', $genCode);
        $this->assertStringNotContainsString('{{', $genCode);
        $this->assertStringNotContainsString('}}', $genCode);
        // vdump($genCode);
    }

    public function testCompileFile_use_all_token():void
    {
        $t = new EasyTemplate();

        $tplFile = Kite::resolve('@testdata/use_all_token.tpl');
        $phpFile = $t->compileFile($tplFile);

        $this->assertNotEmpty($phpFile);

        $genCode = File::readAll($phpFile);
        vdump($genCode);

        $this->assertStringContainsString('<?php', $genCode);
        $this->assertStringContainsString('<?=', $genCode);
        $this->assertStringNotContainsString('{{', $genCode);
        $this->assertStringNotContainsString('}}', $genCode);
    }

    public function testCompileCode_check():void
    {
        $t2 = new EasyTemplate();

        $compiled = $t2->compileCode('');
        $this->assertEquals('', $compiled);

        $compiled = $t2->compileCode('no tpl tags');
        $this->assertEquals('no tpl tags', $compiled);
    }

    private $tplVars = [
        'int' => 23,
        'str' => 'a string',
        'arr' => [
            'hello',
            'world',
        ],
        'map' => [
            'key0' => 'map-val0',
            'key1' => 'map-val1',
        ],
    ];

    public function testV2Render_vars():void
    {
        // inline
        $code = '
{{= $ctx.pkgName ?? "org.example.entity" }}
';

        $tokens1 = token_get_all($code);
        vdump($tokens1);

        $tokens2 = PhpToken::tokenize($code);
        vdump($tokens2);

        $tplVars = ['vars' => ['Info', 'Error', 'Warn']];
        $t = new EasyTemplate();

        $result = $t->renderString($code, $tplVars);

        vdump($result);
    }

    public function testV2Render_ifElse():void
    {
        $t = new EasyTemplate();

        $tplFile = Kite::resolve('@resource-tpl/gen-by-parse/gen-go-funcs2.tpl');
        $tplVars = ['vars' => ['Info', 'Error', 'Warn']];
        \vdump($tplFile);

        $result = $t->renderFile($tplFile, $tplVars);

        vdump($result);
    }

    public function testV2Render_foreach():void
    {
        $t = new EasyTemplate();

        $tplFile = Kite::resolve('@resource-tpl/gen-by-parse/gen-go-funcs2.tpl');
        $tplVars = ['vars' => ['Info', 'Error', 'Warn']];
        \vdump($tplFile);

        $result = $t->renderFile($tplFile, $tplVars);

        vdump($result);
    }
}
